<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Server\ServerProvider;
use BaseFrame\System\Locale;

/**
 * класс описывает все действия связанные с аутентификацией по почте
 * @package Compass\Pivot
 */
class Domain_User_Action_Auth_Mail {

	/**
	 * Получаем user_id по почте
	 *
	 * @return int
	 */
	public static function resolveUserID(string $mail):int {

		try {
			$user_id = Domain_User_Entity_Mail::get($mail)->user_id;
		} catch (Domain_User_Exception_Mail_NotFound) {
			$user_id = 0;
		}

		return $user_id;
	}

	/**
	 * Включена ли 2fa проверка
	 *
	 * @return bool
	 * @throws ParseFatalException
	 */
	public static function is2FAEnabled(int $auth_type):bool {

		return match ($auth_type) {
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_REGISTER_BY_MAIL => Domain_User_Entity_Auth_Config::isMailRegistration2FAEnabled(),
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_LOGIN_BY_MAIL    => Domain_User_Entity_Auth_Config::isMailAuthorization2FAEnabled(),
			default                                                        => throw new ParseFatalException("unexpected auth_type [$auth_type]"),
		};
	}

	/**
	 * Начинаем регистрацию
	 *
	 * @return array
	 */
	public static function beginRegistration(string $mail):Domain_User_Entity_AuthStory {

		// тип аутентификации
		$auth_type = Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_REGISTER_BY_MAIL;

		// создаем все необходимые сущности аутентификации
		$expires_at     = time() + Domain_User_Entity_AuthStory_MethodHandler_Mail::STORY_LIFE_TIME;
		$auth_mail_data = Domain_User_Entity_AuthStory_MethodHandler_Mail::prepareAuthMailDataDraft($mail);
		$story          = Domain_User_Entity_AuthStory::create(0, $auth_type, $expires_at, $auth_mail_data);
		$story->storeInSessionCache();

		return $story;
	}

	/**
	 * Начинаем логин
	 *
	 * @return array
	 */
	public static function beginLogin(int $user_id, string $mail):Domain_User_Entity_AuthStory {

		// тип аутентификации
		$auth_type = Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_LOGIN_BY_MAIL;

		// создаем все необходимые сущности аутентификации
		$expires_at     = time() + Domain_User_Entity_AuthStory_MethodHandler_Mail::STORY_LIFE_TIME;
		$auth_mail_data = Domain_User_Entity_AuthStory_MethodHandler_Mail::prepareAuthMailDataDraft($mail);
		$story          = Domain_User_Entity_AuthStory::create($user_id, $auth_type, $expires_at, $auth_mail_data);
		$story->storeInSessionCache();

		return $story;
	}

	/**
	 * Начинаем восстановление пароля через почту для неавторизованного пользователя
	 *
	 * @return array
	 */
	public static function beginResetPassword(int $user_id, string $mail):Domain_User_Entity_AuthStory {

		// отправляем проверочный код на почту
		$confirm_code = generateConfirmCode();
		$mail_id      = generateUUID();
		self::_sendConfirmCode($confirm_code, $mail_id, Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_RESET_PASSWORD_BY_MAIL, $mail);

		// тип аутентификации
		$auth_type = Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_RESET_PASSWORD_BY_MAIL;

		// создаем все необходимые сущности аутентификации
		$expires_at     = time() + Domain_User_Entity_AuthStory_MethodHandler_Mail::STORY_LIFE_TIME;
		$auth_mail_data = Domain_User_Entity_AuthStory_MethodHandler_Mail::prepareAuthMailDataWithConfirmCodeDraft($mail, $confirm_code, $mail_id);
		$story          = Domain_User_Entity_AuthStory::create($user_id, $auth_type, $expires_at, $auth_mail_data);
		$story->storeInSessionCache();

		// сохраняем в кэш проверочный код и пароль
		Domain_User_Entity_CachedConfirmCode::storeMailAuthFullScenarioParams($confirm_code, "", Domain_User_Entity_AuthStory_MethodHandler_Mail::STORY_LIFE_TIME);

		return $story;
	}

	/**
	 * При неправильном вводе пароля
	 *
	 * @return Domain_User_Entity_AuthStory
	 * @throws \parseException
	 */
	public static function onWrongPassword(Domain_User_Entity_AuthStory $story):Domain_User_Entity_AuthStory {

		$story->getAuthMailHandler()->handleWrongPassword();
		$story->storeInSessionCache();

		return $story;
	}

	/**
	 * При неправильном вводе кода
	 *
	 * @return Domain_User_Entity_AuthStory
	 * @throws \parseException
	 */
	public static function onWrongCode(Domain_User_Entity_AuthStory $story):Domain_User_Entity_AuthStory {

		$story->getAuthMailHandler()->handleWrongCode();
		$story->storeInSessionCache();

		return $story;
	}

	/**
	 * проверяем, что сценарий short_confirm допустим
	 *
	 * @throws Domain_User_Exception_AuthStory_Mail_ShortConfirmScenarioNotAllowed
	 * @throws ParseFatalException
	 */
	public static function assertShortConfirmScenarioAllowed(int $auth_type):void {

		// игнорируем проверку в тестах, если нужно
		if (ServerProvider::isTest() && Type_System_Testing::isIgnoreMailAuthScenarioException()) {
			return;
		}

		if (static::is2FAEnabled($auth_type)) {
			throw new Domain_User_Exception_AuthStory_Mail_ShortConfirmScenarioNotAllowed("short_confirm scenario not allowed");
		}
	}

	/**
	 * проверяем, что сценарий full_confirm допустим
	 *
	 * @throws Domain_User_Exception_AuthStory_Mail_FullConfirmScenarioNotAllowed
	 * @throws ParseFatalException
	 */
	public static function assertFullConfirmScenarioAllowed(int $auth_type):void {

		// игнорируем проверку в тестах, если нужно
		if (ServerProvider::isTest() && Type_System_Testing::isIgnoreMailAuthScenarioException()) {
			return;
		}

		if (!static::is2FAEnabled($auth_type)) {
			throw new Domain_User_Exception_AuthStory_Mail_FullConfirmScenarioNotAllowed("full_confirm scenario not allowed");
		}
	}

	/**
	 * проверяем пароль независимо от типа аутентификации (внутри развилка)
	 *
	 * @return Domain_User_Entity_AuthStory
	 * @throws Domain_User_Exception_Mail_NotFound
	 * @throws ParseFatalException
	 */
	public static function verifyPasswordOnShortScenario(Domain_User_Entity_AuthStory $story, string $password):Domain_User_Entity_AuthStory {

		return match ($story->getType()) {
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_REGISTER_BY_MAIL => static::verifyPasswordOnShortScenarioRegistration($story, $password),
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_LOGIN_BY_MAIL    => static::verifyPasswordOnShortScenarioLogin($story, $password),
			default                                                        => throw new ParseFatalException("unexpected auth_type"),
		};
	}

	/**
	 * проверяем пароль в случае регистрации по short_scenario
	 *
	 * @return Domain_User_Entity_AuthStory
	 * @throws ParseFatalException
	 */
	public static function verifyPasswordOnShortScenarioRegistration(Domain_User_Entity_AuthStory $story, string $password):Domain_User_Entity_AuthStory {

		$auth_type = $story->getType();

		// проверяем, что это short_scenario
		try {
			self::assertShortConfirmScenarioAllowed($auth_type);
		} catch (Domain_User_Exception_AuthStory_Mail_ShortConfirmScenarioNotAllowed) {
			throw new ParseFatalException("unexpected behaviour");
		}

		// обязательно проверяем, что это регистрация, так как фактически здесь не проверяется пароль (при регистрации он не существует)
		if ($auth_type !== Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_REGISTER_BY_MAIL) {
			throw new ParseFatalException("unexpected behaviour");
		}

		// фиксируем, что попытка аутентификации прошла этап подтверждения пароля
		$story->getAuthMailHandler()->handleSuccessPassword([]);

		return $story;
	}

	/**
	 * проверяем пароль в случае логина по short_scenario
	 *
	 * @return Domain_User_Entity_AuthStory
	 * @throws Domain_User_Exception_Mail_NotFound
	 */
	public static function verifyPasswordOnShortScenarioLogin(Domain_User_Entity_AuthStory $story, string $password):Domain_User_Entity_AuthStory {

		// проверяем, что это short_scenario
		try {
			self::assertShortConfirmScenarioAllowed($story->getType());
		} catch (Domain_User_Exception_AuthStory_Mail_ShortConfirmScenarioNotAllowed) {
			throw new ParseFatalException("unexpected behaviour");
		}

		// получаем запись почты пользователя с хэшированным паролем
		$user_mail = Domain_User_Entity_Mail::get($story->getAuthMailHandler()->getMail());

		// сверяем пароль
		Domain_User_Entity_Password::assertPassword($password, $user_mail);

		// фиксируем, что попытка аутентификации прошла этап подтверждения пароля
		$story->getAuthMailHandler()->handleSuccessPassword([]);

		return $story;
	}

	/**
	 * проверяем пароль независимо от типа аутентификации (внутри развилка)
	 *
	 * @return Domain_User_Entity_AuthStory
	 * @throws Domain_User_Exception_Mail_NotFound
	 * @throws ParseFatalException
	 */
	public static function verifyPasswordOnFullScenario(Domain_User_Entity_AuthStory $story, string $password):Domain_User_Entity_AuthStory {

		return match ($story->getType()) {
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_REGISTER_BY_MAIL => static::verifyPasswordOnFullScenarioRegistration($story, $password),
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_LOGIN_BY_MAIL    => static::verifyPasswordOnFullScenarioLogin($story, $password),
			default                                                        => throw new ParseFatalException("unexpected auth_type"),
		};
	}

	/**
	 * проверяем пароль в случае регистрации по full_scenario
	 *
	 * внимание! пользователь может вернуться с этапа ввода кода на этап ввода пароля
	 *
	 * @return Domain_User_Entity_AuthStory
	 * @throws ParseFatalException
	 */
	public static function verifyPasswordOnFullScenarioRegistration(Domain_User_Entity_AuthStory $story, string $password):Domain_User_Entity_AuthStory {

		$auth_type = $story->getType();

		// проверяем, что это full_scenario
		try {
			self::assertFullConfirmScenarioAllowed($auth_type);
		} catch (Domain_User_Exception_AuthStory_Mail_FullConfirmScenarioNotAllowed) {
			throw new ParseFatalException("unexpected behaviour");
		}

		// обязательно проверяем, что это регистрация, так как фактически здесь не проверяется пароль (при регистрации он не существует)
		if ($auth_type !== Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_REGISTER_BY_MAIL) {
			throw new ParseFatalException("unexpected behaviour");
		}

		// если проверочный код не отправлялся ранее
		if (!$story->getAuthMailHandler()->wasConfirmCodeSent()) {

			// отправляем проверочный код на почту
			$confirm_code = generateConfirmCode();
			$mail_id      = generateUUID();
			self::_sendConfirmCode($confirm_code, $mail_id, $auth_type, $story->getAuthMailHandler()->getMail());

			// обновляем попытку аутентификации
			$story = self::_handleSuccessPasswordOnFullScenario($story, $confirm_code, $mail_id);
			$story->storeInSessionCache();
		} else {

			// кейс, когда пользователь вернулся на шаг назад с этапа ввода кода
			// проверочный код у него уже есть – достанем его из кэша, чтобы дальше по коду записать новый пароль

			// достаем ранее отправленный проверочный код
			[$confirm_code, $previus_passed_password] = Domain_User_Entity_CachedConfirmCode::getMailAuthFullScenarioParams();

			// если доступна переотправка кода, то сделаем это
			if ($story->getAuthMailHandler()->resendIsAvailable()) {

				$mail_id = generateUUID();
				self::_sendConfirmCode($confirm_code, $mail_id, $auth_type, $story->getAuthMailHandler()->getMail());

				// обновляем попытку аутентификации
				$story->getAuthMailHandler()->handleSuccessPassword([
					"resend_count"   => $story->getAuthMailHandler()->getResendCount() + 1,
					"next_resend_at" => time() + Domain_User_Entity_AuthStory_MethodHandler_Mail::NEXT_ATTEMPT_AFTER,
					"message_id"     => $mail_id,
				]);
				$story->storeInSessionCache();
			}
		}

		// сохраняем в кэш проверочный код и пароль
		Domain_User_Entity_CachedConfirmCode::storeMailAuthFullScenarioParams($confirm_code, $password, Domain_User_Entity_AuthStory_MethodHandler_Mail::STORY_LIFE_TIME);

		return $story;
	}

	/**
	 * проверяем пароль в случае логина по full_scenario
	 *
	 * @return Domain_User_Entity_AuthStory
	 * @throws Domain_User_Exception_Mail_NotFound
	 */
	public static function verifyPasswordOnFullScenarioLogin(Domain_User_Entity_AuthStory $story, string $password):Domain_User_Entity_AuthStory {

		// проверяем, что это full_scenario
		try {
			self::assertFullConfirmScenarioAllowed($story->getType());
		} catch (Domain_User_Exception_AuthStory_Mail_FullConfirmScenarioNotAllowed) {
			throw new ParseFatalException("unexpected behaviour");
		}

		// получаем запись почты пользователя с хэшированным паролем
		$user_mail = Domain_User_Entity_Mail::get($story->getAuthMailHandler()->getMail());

		// сверяем пароль
		Domain_User_Entity_Password::assertPassword($password, $user_mail);

		// отправляем проверочный код на почту
		$confirm_code = generateConfirmCode();
		$mail_id      = generateUUID();
		self::_sendConfirmCode($confirm_code, $mail_id, $story->getType(), $story->getAuthMailHandler()->getMail());

		// обновляем попытку аутентификации
		$story = self::_handleSuccessPasswordOnFullScenario($story, $confirm_code, $mail_id);
		$story->storeInSessionCache();

		// сохраняем в кэш проверочный код и пароль
		Domain_User_Entity_CachedConfirmCode::storeMailAuthFullScenarioParams($confirm_code, $password, Domain_User_Entity_AuthStory_MethodHandler_Mail::STORY_LIFE_TIME);

		return $story;
	}

	/**
	 * отправляем проверочный код
	 *
	 * @return array
	 */
	protected static function _sendConfirmCode(string $confirm_code, string $mail_id, int $auth_type, string $mail):array {

		// получаем конфиг с шаблонами для писем
		$config = getConfig("LOCALE_TEXT");

		// формируем заголовок и содержимое письма
		$template = match ($auth_type) {
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_REGISTER_BY_MAIL       => Type_Mail_Content::TEMPLATE_MAIL_REGISTRATION,
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_LOGIN_BY_MAIL          => Type_Mail_Content::TEMPLATE_MAIL_AUTHORIZATION,
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_RESET_PASSWORD_BY_MAIL => Type_Mail_Content::TEMPLATE_MAIL_RESTORE,
			default                                                              => throw new ParseFatalException("unexpected auth story type [{$auth_type}]"),
		};
		[$title, $content] = Type_Mail_Content::make($config, $template, Locale::LOCALE_RUSSIAN, [
			"confirm_code" => addConfirmCodeDash($confirm_code),
		]);

		// добавляем задачу на отправку
		Type_Mail_Queue::addTask($mail_id, $mail, $title, $content, []);

		return [$confirm_code, $mail_id];
	}

	/**
	 * обновляем попытку аутентификации
	 *
	 * @return Domain_User_Entity_AuthStory
	 * @throws cs_IncorrectSaltVersion
	 */
	protected static function _handleSuccessPasswordOnFullScenario(Domain_User_Entity_AuthStory $story, string $confirm_code, string $mail_id):Domain_User_Entity_AuthStory {

		// фиксируем, что попытка аутентификации прошла этап подтверждения пароля
		// отправляем на этап ввода кода
		$story->getAuthMailHandler()->handleSuccessPassword([
			"resend_count"   => 0,
			"next_resend_at" => time() + Domain_User_Entity_AuthStory_MethodHandler_Mail::NEXT_ATTEMPT_AFTER,
			"message_id"     => $mail_id,
			"code_hash"      => Type_Hash_Code::makeHash($confirm_code),
		]);

		return $story;
	}

	/**
	 * переотправляем проверочный код
	 *
	 * @return Domain_User_Entity_AuthStory
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @throws cs_IncorrectSaltVersion
	 */
	public static function resendCode(Domain_User_Entity_AuthStory $story):Domain_User_Entity_AuthStory {

		// получаем текущий проверочный код
		try {

			/** @noinspection PhpUnusedLocalVariableInspection */
			[$confirm_code, $password] = Domain_User_Entity_CachedConfirmCode::getMailAuthFullScenarioParams();
		} catch (cs_CacheIsEmpty) {

			// если в кэше не нашли проверочный код – логируем и генерируем новый
			Type_System_Admin::log("mail_auth_story_resend", "не нашли проверочный код в кэше для {$story->getAuthMap()}");
			$confirm_code = generateConfirmCode();
		}

		// переотправляем письмо
		$mail_id = generateUUID();
		self::_sendConfirmCode($confirm_code, $mail_id, $story->getType(), $story->getAuthMailHandler()->getMail());

		$story->getAuthMailHandler()->handleResend($confirm_code, $mail_id);
		$story->storeInSessionCache();

		return $story;
	}

	/**
	 * подтверждаем успешное подтверждение кода при сбросе пароля
	 *
	 * @return Domain_User_Entity_AuthStory
	 * @throws ParseFatalException
	 */
	public static function confirmResetPassword(Domain_User_Entity_AuthStory $story):Domain_User_Entity_AuthStory {

		$story->getAuthMailHandler()->handleSuccessCode();
		$story->storeInSessionCache();

		return $story;
	}

	/**
	 * сбрасываем пароль
	 *
	 * @throws Domain_User_Exception_Mail_NotFound
	 * @throws \parseException
	 * @throws cs_DamagedActionException
	 */
	public static function resetPassword(int $user_id, string $mail, string $password):void {

		// получаем запись с паролем
		$user_mail = Domain_User_Entity_Mail::get($mail);

		// если не совпал user_id
		if ($user_mail->user_id !== $user_id) {
			throw new cs_DamagedActionException();
		}

		// обновляем пароль
		Domain_User_Entity_Mail::updatePassword($mail, Domain_User_Entity_Password::makeHash($password));
	}

	/**
	 * разрешена ли регистрация по переданному адресу почты
	 *
	 * @throws Domain_User_Exception_AuthStory_Mail_DomainNotAllowed
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\InvalidMail
	 */
	public static function assertAllowRegistration(string $mail):void {

		// получаем список разрешенных для регистрации доменов
		$allowed_domain_list = Domain_User_Entity_Auth_Config::getMailAllowedDomainList();

		// если список пуст, то не делаем никаких проверок
		if (count($allowed_domain_list) < 1) {
			return;
		}

		// проверяем наличие домена в списке разрешенных
		$domain = (new \BaseFrame\System\Mail($mail))->getDomain();
		if (!in_array($domain, $allowed_domain_list)) {
			throw new Domain_User_Exception_AuthStory_Mail_DomainNotAllowed();
		}
	}

	/**
	 * проверяем кол-во ввода невалидного пароля
	 *
	 * @throws cs_AuthIsBlocked
	 */
	public static function checkInvalidConfirmPasswordLimit(Domain_User_Entity_AuthStory $story):void {

		// если это не логин, то проверки лимита нет
		if ($story->getType() !== Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_LOGIN_BY_MAIL) {
			return;
		}

		// получаем user_id пользователя под которым авторизуются
		$user_id = $story->getUserId();

		// проверяем блокировку
		try {
			Type_Antispam_User::check($user_id, Type_Antispam_User::AUTH_MAIL_ENTERING_PASSWORD);
		} catch (BlockException $e) {
			throw new cs_AuthIsBlocked($e->getExpire());
		}
	}

	/**
	 * увеличиваем кол-во ввода невалидного пароля
	 *
	 * @return int оставшееся кол-во попыток ввода пароля
	 *
	 * @throws cs_AuthIsBlocked
	 */
	public static function incInvalidConfirmPasswordLimit(Domain_User_Entity_AuthStory $story):int {

		// если это не логин, то ничего не делаем
		if ($story->getType() !== Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_LOGIN_BY_MAIL) {

			// кроме как в логине данный кейс нигде не встречается, поэтому справедливо кинуть исключение
			throw new ParseFatalException("unexpected behaviour");
		}

		// получаем user_id пользователя под которым авторизуются
		$user_id = $story->getUserId();

		// проверяем и увеличиваем блокировку
		try {
			$count = Type_Antispam_User::throwIfBlocked($user_id, Type_Antispam_User::AUTH_MAIL_ENTERING_PASSWORD);
		} catch (BlockException $e) {
			throw new cs_AuthIsBlocked($e->getExpire());
		}

		return max(Type_Antispam_User::getBlockKeyLimit(Type_Antispam_User::AUTH_MAIL_ENTERING_PASSWORD) - $count, 0);
	}

	/**
	 * требуем/проверяем капчу при вводе пароля
	 *
	 * @throws ParseFatalException
	 * @throws cs_PlatformNotFound
	 * @throws cs_RecaptchaIsRequired
	 * @throws cs_WrongRecaptcha
	 */
	public static function checkCaptchaOnEnteringPassword(Domain_User_Entity_AuthStory $story, string|bool $grecaptcha_response):void {

		// если это не логин, то ничего не делаем
		if ($story->getType() !== Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_LOGIN_BY_MAIL) {
			return;
		}

		// получаем кол-во ошибок при вводе пароля
		$password_error_count = $story->getAuthMailHandler()->getPasswordErrorCount();

		// если кол-во вводов пароля превышает отметки после которой запрашиваем капчу – то запрашиваем ее
		if ($password_error_count >= Domain_User_Entity_Auth_Config::getCaptchaRequireAfter()) {
			Type_Captcha_Main::assertCaptcha($grecaptcha_response, true);
		}
	}
}