<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\InvalidMail;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Сценарии для работы с безопасностью через почту авторизованным пользователем
 */
class Domain_User_Scenario_Api_Security_Mail {

	public const SCENARIO_SHORT_ADD = "short_add";
	public const SCENARIO_FULL_ADD  = "full_add";

	public const SCENARIO_SHORT_CHANGE = "short_change";
	public const SCENARIO_FULL_CHANGE  = "full_change";

	public const STAGE_ENTERING_PASSWORD = "entering_password";
	public const STAGE_ENTERING_CODE     = "entering_code";

	##########################################################
	# region - смена пароля
	##########################################################

	/**
	 * меняем пароль
	 */

	/**
	 * @throws Domain_User_Exception_Mail_NotFound
	 * @throws Domain_User_Exception_Password_Incorrect
	 * @throws Domain_User_Exception_Password_IncorrectNew
	 * @throws Domain_User_Exception_Password_Mismatch
	 * @throws \queryException|ParseFatalException|Domain_User_Exception_AuthMethodDisabled
	 */
	public static function changePassword(int $user_id, string $session_uniq, string $password, string $password_new):void {

		// проверяем аутентификация по почте включена в конфиге
		Domain_User_Entity_Auth_Method::assertMethodEnabled(Domain_User_Entity_Auth_Method::METHOD_MAIL);

		// получаем почту пользователя и проверяем что она действительно есть
		try {
			$user_security = Gateway_Db_PivotUser_UserSecurity::getOne($user_id);
		} catch (\cs_RowIsEmpty) {
			throw new ParseFatalException("user not found");
		}
		Domain_User_Entity_Mail::assertAlreadyExistMail($user_security);

		// проверяем введенные строки паролей на корректность
		Domain_User_Entity_Password::throwIfIncorrect($password);
		Domain_User_Entity_Password::throwIfIncorrectNew($password_new);

		// начинаем процесс
		$password_mail_story      = Domain_User_Action_Password_Mail::beginStory($user_id, $session_uniq, Domain_User_Entity_PasswordMail_Story::TYPE_CHANGE_PASSWORD);
		$password_mail_story_data = $password_mail_story->getStoryData();

		// меняем пароль
		Domain_User_Action_Password_Mail::changePassword(
			$user_id, $user_security->mail, $password, $password_new, $password_mail_story_data->password_mail_story_id
		);

		// удаляем процесс из кеша
		Domain_User_Action_Password_Mail::deleteStory($session_uniq, Domain_User_Entity_PasswordMail_Story::TYPE_CHANGE_PASSWORD);
	}

	/**
	 * Метод для начала процесса сброса пароля
	 *
	 * @throws BlockException
	 * @throws Domain_User_Exception_Mail_NotFound
	 * @throws Domain_User_Exception_Password_ErrorCountLimitExceeded
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws cs_blockException|Domain_User_Exception_AuthMethodDisabled
	 */
	public static function tryResetPassword(int $user_id, string $session_uniq):array {

		// проверяем аутентификация по почте включена в конфиге
		Domain_User_Entity_Auth_Method::assertMethodEnabled(Domain_User_Entity_Auth_Method::METHOD_MAIL);

		// проверяем наличие почты у пользователя
		try {
			$user_security = Gateway_Db_PivotUser_UserSecurity::getOne($user_id);
		} catch (\cs_RowIsEmpty) {
			throw new ParseFatalException("user not found");
		}
		Domain_User_Entity_Mail::assertAlreadyExistMail($user_security);

		try {

			$story = Domain_User_Entity_PasswordMail_Story::getFromSessionCache($session_uniq, Domain_User_Entity_PasswordMail_Story::TYPE_RESET_PASSWORD)
				->assertUserAuthorized($user_id)
				->assertNotExpired()
				->assertActive()
				->assertNotSuccess();

			try {

				// получаем запись
				$code_story = $story->getActiveCodeStoryEntity()->assertCodeErrorCountLimitNotExceeded()->assertActive();
			} catch (Domain_User_Exception_Password_ErrorCountLimitExceeded $e) {

				$e->setNextAttempt($story->getExpiresAt());
				throw $e;
			}
		} catch (cs_CacheIsEmpty|Domain_User_Exception_Password_StoryIsExpired|Domain_User_Exception_Password_StoryIsNotActive|
		Domain_User_Exception_Password_StoryIsSuccess|Domain_User_Exception_Password_NotFound|Domain_User_Exception_Password_StoryIsNotActive) {

			// создаем новый процесс
			$story                  = Domain_User_Entity_PasswordMail_Story::createNewStory(
				$user_id, $session_uniq, Domain_User_Entity_PasswordMail_Story::TYPE_RESET_PASSWORD
			);
			$password_mail_story_id = Gateway_Db_PivotMail_MailPasswordStory::insert($story->getStoryData());
			$story                  = Domain_User_Entity_PasswordMail_Story::updateStory(
				$story->getStoryData(), ["password_mail_story_id" => $password_mail_story_id]
			);
			$story->storeInSessionCache($session_uniq, Domain_User_Entity_PasswordMail_Story::TYPE_RESET_PASSWORD);

			// получаем story отправки кода
			$code_story = Domain_User_Action_Password_Mail::beginResetPassword($story->getStoryData()->password_mail_story_id, $session_uniq, $user_security->mail);
		}

		return [$story, $code_story];
	}

	/**
	 * Метод подтверждение сброса пароля кодом подтверждения с почты
	 *
	 * @throws Domain_User_Exception_AuthMethodDisabled
	 * @throws Domain_User_Exception_Mail_NotFound
	 * @throws Domain_User_Exception_Password_ErrorCountLimitExceeded
	 * @throws Domain_User_Exception_Password_NotFound
	 * @throws Domain_User_Exception_Password_StoryIsExpired
	 * @throws Domain_User_Exception_Password_StoryIsNotActive
	 * @throws Domain_User_Exception_Password_StoryIsSuccess
	 * @throws Domain_User_Exception_Password_WrongMap
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @throws cs_InvalidConfirmCode
	 * @throws cs_WrongCode
	 */
	public static function confirmResetPassword(int $user_id, string $session_uniq, string $code, string $password_mail_story_map):void {

		// проверяем аутентификация по почте включена в конфиге
		Domain_User_Entity_Auth_Method::assertMethodEnabled(Domain_User_Entity_Auth_Method::METHOD_MAIL);

		// валидируем код
		Domain_User_Entity_Validator::assertValidConfirmCode($code);

		// проверяем наличие почты у пользователя
		try {
			$user_security = Gateway_Db_PivotUser_UserSecurity::getOne($user_id);
		} catch (\cs_RowIsEmpty) {
			throw new ParseFatalException("user not found");
		}
		Domain_User_Entity_Mail::assertAlreadyExistMail($user_security);

		// получаем запись аутентификации
		$story = Domain_User_Entity_PasswordMail_Story::getByMap($password_mail_story_map);

		// делаем общие проверки
		$story->assertNotExpired()
			->assertActive()
			->assertNotSuccess()
			->assertUserAuthorized($user_id);

		$code_story = $story->getActiveCodeStoryEntity();

		try {
			$code_story->assertCodeErrorCountLimitNotExceeded()->assertEqualCode($code);
		} catch (cs_WrongCode $e) {

			// записываем в историю ошибку подтверждения и обновляем кэш
			$story->handleWrongCode($story->getStoryData());
			$code_story->handleWrongCode($code_story->getStoryData());

			$story->storeInSessionCache($session_uniq, Domain_User_Entity_PasswordMail_Story::TYPE_RESET_PASSWORD);

			throw $e;
		}

		// обновляем попытку, указываем что код был подтвержден
		$code_story->handleSuccessCode($code_story->getStoryData());
		$story->storeInSessionCache($session_uniq, Domain_User_Entity_PasswordMail_Story::TYPE_RESET_PASSWORD);
	}

	/**
	 * Метод задания нового пароля для завершения процесса сброса пароля
	 *
	 * @throws Domain_User_Exception_Mail_NotFound
	 * @throws Domain_User_Exception_Password_Incorrect
	 * @throws Domain_User_Exception_Password_NotFound
	 * @throws Domain_User_Exception_Password_StageNotAllowed
	 * @throws Domain_User_Exception_Password_StoryIsExpired
	 * @throws Domain_User_Exception_Password_StoryIsNotActive
	 * @throws Domain_User_Exception_Password_StoryIsSuccess
	 * @throws Domain_User_Exception_Password_WrongMap
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @throws Domain_User_Exception_UserNotAuthorized
	 */
	public static function finishResetPassword(int $user_id, string $session_uniq, string $password, string $password_mail_story_map):void {

		// проверяем аутентификация по почте включена в конфиге
		Domain_User_Entity_Auth_Method::assertMethodEnabled(Domain_User_Entity_Auth_Method::METHOD_MAIL);

		// валидируем пароль
		Domain_User_Entity_Password::throwIfIncorrect($password);

		// проверяем наличие почты у пользователя
		try {
			$user_security = Gateway_Db_PivotUser_UserSecurity::getOne($user_id);
		} catch (\cs_RowIsEmpty) {
			throw new ParseFatalException("user not found");
		}
		Domain_User_Entity_Mail::assertAlreadyExistMail($user_security);

		// получаем запись аутентификации
		$story = Domain_User_Entity_PasswordMail_Story::getByMap($password_mail_story_map);

		// делаем общие проверки
		$story->assertNotExpired()
			->assertActive()
			->assertNotSuccess()
			->assertUserAuthorized($user_id);

		// проверяем что код ввели на прошлом шаге
		$story->getActiveCodeStoryEntity()->assertHasCode();

		// сбрасываем
		Domain_User_Action_Password_Mail::resetPassword($user_id, $user_security->mail, $password);

		// чистим кэш
		Domain_User_Entity_PasswordMail_Story::deleteSessionCache($session_uniq, Domain_User_Entity_PasswordMail_Story::TYPE_RESET_PASSWORD);
		Domain_User_Entity_PasswordMail_CodeStory::deleteSessionCache($session_uniq, Domain_User_Entity_PasswordMail_Story::TYPE_RESET_PASSWORD);

		// обрабатываем успех
		$story->handleSuccess($story->getStoryData());
	}

	##########################################################
	# region - добавление почты
	##########################################################

	/**
	 * добавляем почту (начало процесса)
	 *
	 * @return array
	 * @throws Domain_User_Exception_AuthMethodDisabled
	 * @throws Domain_User_Exception_AuthStory_Mail_DomainNotAllowed
	 * @throws Domain_User_Exception_Mail_AlreadyExist
	 * @throws Domain_User_Exception_Mail_IsTaken
	 * @throws InvalidMail
	 * @throws ParseFatalException
	 * @throws \queryException
	 * @throws Domain_User_Exception_Security_UserWasRegisteredBySso
	 */
	public static function add(int $user_id, string $session_uniq, string $mail):array {

		// проверяем аутентификация по почте включена в конфиге
		Domain_User_Entity_Auth_Method::assertMethodEnabled(Domain_User_Entity_Auth_Method::METHOD_MAIL);

		// проверяем введенные mail на корректность
		$mail = (new \BaseFrame\System\Mail($mail))->mail();

		// проверяем, что разрешен ли для аутентификации данный домен почты
		Domain_User_Action_Auth_Mail::assertAllowRegistration($mail);

		// проверяем что у пользователя еще нет почты
		try {
			$user_security = Gateway_Db_PivotUser_UserSecurity::getOne($user_id);
		} catch (\cs_RowIsEmpty) {
			throw new ParseFatalException("user not found");
		}
		Domain_User_Entity_Mail::assertNotExistMail($user_security);

		// проверяем что пользователь не зарегистрирован через SSO
		Domain_User_Entity_Mail::assertUserWasNotRegisteredBySso($user_id);

		// проверяем что данная почта никем не занята
		Domain_User_Entity_Mail::assertMailNotTaken($mail);

		// получаем/начинаем процесс
		try {

			$story      = Domain_User_Entity_Security_AddMail_Story::getFromSessionCache($mail)->assertNotExpire()->assertNotSuccess();
			$story_code = Domain_User_Entity_Security_AddMail_CodeStory::getFromSessionCache($mail)->assertNotSuccess();
		} catch (cs_CacheIsEmpty|Domain_User_Exception_Mail_StoryIsExpired|Domain_User_Exception_Mail_StoryIsSuccess) {
			[$story, $story_code] = Domain_User_Action_Security_AddMail_CreateStory::do($user_id, $session_uniq, $mail);
		}

		// меняем stage в истории
		Domain_User_Action_Mail_Add::updateStage($story->getStoryMap(), Domain_User_Entity_Security_AddMail_Story::STAGE_SET_PASSWORD);

		return [
			$story,
			$story_code,
			Domain_User_Entity_Auth_Config::isMailAuthorization2FAEnabled() ? self::SCENARIO_FULL_ADD : self::SCENARIO_SHORT_ADD,
			self::STAGE_ENTERING_PASSWORD,
		];
	}

	/**
	 * устанавливаем пароль при добавлении почты (короткий сценарий - завершение процесса добавления почты)
	 *
	 * @throws Domain_User_Exception_AuthMethodDisabled
	 * @throws Domain_User_Exception_AuthStory_Mail_DomainNotAllowed
	 * @throws Domain_User_Exception_Mail_AlreadyExist
	 * @throws Domain_User_Exception_Mail_IsTaken
	 * @throws Domain_User_Exception_Mail_ScenarioNotAllowed
	 * @throws Domain_User_Exception_Mail_StoryIsExpired
	 * @throws Domain_User_Exception_Mail_StoryIsNotActive
	 * @throws Domain_User_Exception_Mail_StoryIsSuccess
	 * @throws Domain_User_Exception_Mail_StoryNotFound
	 * @throws Domain_User_Exception_Password_Incorrect
	 * @throws ParseFatalException
	 * @throws \cs_DecryptHasFailed
	 * @throws \parseException
	 */
	public static function setPasswordOnShortAdd(int $user_id, string $add_mail_story_key, string $password):string {

		$add_mail_story_map = Type_Pack_AddMailStory::doDecrypt($add_mail_story_key);

		// проверяем аутентификация по почте включена в конфиге
		Domain_User_Entity_Auth_Method::assertMethodEnabled(Domain_User_Entity_Auth_Method::METHOD_MAIL);

		// проверяем совпадает ли сценарий с тем что установлен на сервере
		Domain_User_Entity_Mail::assertShortScenario();

		// проверяем введенные строки паролей на корректность
		Domain_User_Entity_Password::throwIfIncorrect($password);

		// достаем адрес почты процесса
		try {
			$mail = Type_Pack_AddMailStory::getMail($add_mail_story_map);
		} catch (\cs_UnpackHasFailed) {
			throw new ParseFatalException("mail not found");
		}

		// устанавливаем пароль при добавлении почты
		Domain_User_Action_Mail_Add::doSetPasswordOnShort($user_id, $add_mail_story_map, $mail, $password);

		// отправляем ивент пользователю о добавлении почты
		Gateway_Bus_SenderBalancer::mailAdded($user_id, $mail);

		return $mail;
	}

	/**
	 * устанавливаем пароль при добавлении почты (полный сценарий - второй шаг)
	 *
	 * @return array
	 * @throws Domain_User_Exception_AuthStory_Mail_DomainNotAllowed
	 * @throws Domain_User_Exception_AuthMethodDisabled
	 * @throws Domain_User_Exception_Mail_AlreadyExist
	 * @throws Domain_User_Exception_Mail_IsTaken
	 * @throws Domain_User_Exception_Mail_StoryIsExpired
	 * @throws Domain_User_Exception_Mail_StoryIsNotActive
	 * @throws Domain_User_Exception_Mail_StoryIsSuccess
	 * @throws Domain_User_Exception_Mail_StoryNotFound
	 * @throws Domain_User_Exception_Password_Incorrect
	 * @throws \cs_DecryptHasFailed
	 * @throws \queryException
	 */
	public static function setPasswordOnFullAdd(int $user_id, string $add_mail_story_key, string $password):array {

		$add_mail_story_map = Type_Pack_AddMailStory::doDecrypt($add_mail_story_key);

		// проверяем аутентификация по почте включена в конфиге
		Domain_User_Entity_Auth_Method::assertMethodEnabled(Domain_User_Entity_Auth_Method::METHOD_MAIL);

		// проверяем совпадает ли сценарий с тем что установлен на сервере
		Domain_User_Entity_Mail::assertFullScenario();

		// проверяем введенные строки паролей на корректность
		Domain_User_Entity_Password::throwIfIncorrect($password);

		// достаем адрес почты процесса
		try {
			$mail = Type_Pack_AddMailStory::getMail($add_mail_story_map);
		} catch (\cs_UnpackHasFailed) {
			throw new ParseFatalException("mail not found");
		}

		// устанавливаем пароль
		try {
			[$story, $story_code] = Domain_User_Action_Mail_Add::doSetPasswordOnFullAdd($user_id, $add_mail_story_map, $mail, $password);
		} catch (Domain_User_Exception_AuthStory_Mail_DomainNotAllowed|Domain_User_Exception_Mail_IsTaken|Domain_User_Exception_Mail_AlreadyExist
		|Domain_User_Exception_Mail_AlreadyExist|Domain_User_Exception_UserNotAuthorized|Domain_User_Exception_Mail_StoryNotFound
		|Domain_User_Exception_Mail_StoryIsExpired|Domain_User_Exception_Mail_StoryIsNotActive|Domain_User_Exception_Mail_StoryIsSuccess $e) {

			Domain_User_Action_Mail_Add::incErrorCount($add_mail_story_map);
			throw $e;
		}

		return [$story, $story_code, self::SCENARIO_FULL_ADD, self::STAGE_ENTERING_CODE];
	}

	/**
	 * подтверждаем проверочным кодом добавлении почты (полный сценарий - завершение процесса добавления почты)
	 *
	 * @throws Domain_User_Exception_AuthMethodDisabled
	 * @throws Domain_User_Exception_AuthStory_Mail_DomainNotAllowed
	 * @throws Domain_User_Exception_Mail_AlreadyExist
	 * @throws Domain_User_Exception_Mail_CodeErrorCountExceeded
	 * @throws Domain_User_Exception_Mail_IsTaken
	 * @throws Domain_User_Exception_Mail_StoryIsNotActive
	 * @throws Domain_User_Exception_Mail_StoryIsSuccess
	 * @throws Domain_User_Exception_Mail_StoryNotEqualStage
	 * @throws Domain_User_Exception_Mail_StoryNotFound
	 * @throws ParseFatalException
	 * @throws \cs_DecryptHasFailed
	 * @throws \parseException
	 * @throws cs_CacheIsEmpty
	 * @throws cs_WrongCode
	 */
	public static function confirmCodeOnFullAdd(int $user_id, string $add_mail_story_key, string $code):string {

		$add_mail_story_map = Type_Pack_AddMailStory::doDecrypt($add_mail_story_key);

		// проверяем аутентификация по почте включена в конфиге
		Domain_User_Entity_Auth_Method::assertMethodEnabled(Domain_User_Entity_Auth_Method::METHOD_MAIL);

		// проверяем совпадает ли сценарий с тем что установлен на сервере
		Domain_User_Entity_Mail::assertFullScenario();

		// достаем адрес почты процесса
		try {
			$mail = Type_Pack_AddMailStory::getMail($add_mail_story_map);
		} catch (\cs_UnpackHasFailed) {
			throw new ParseFatalException("mail not found");
		}

		// выполняем процесс
		Domain_User_Action_Mail_Add::doConfirmCodeOnFullAdd($user_id, $add_mail_story_map, $mail, $code);

		// отправляем ивент пользователю о добавлении почты
		Gateway_Bus_SenderBalancer::mailAdded($user_id, $mail);

		// удаляем процесс из кеша
		Domain_User_Entity_Security_AddMail_Story::deleteSessionCache($mail);

		return $mail;
	}

	##########################################################
	# region - переотправка проверочного кода на почту
	##########################################################

	/**
	 * Метод переотправки проверочного кода по почте для авторизованного пользователя
	 *
	 * @return array
	 * @throws Domain_User_Exception_AuthMethodDisabled
	 * @throws Domain_User_Exception_Mail_AlreadyExist
	 * @throws Domain_User_Exception_Mail_CodeResendCountExceeded
	 * @throws Domain_User_Exception_Mail_CodeResendNotAvailable
	 * @throws Domain_User_Exception_Mail_NotFound
	 * @throws Domain_User_Exception_Mail_StoryIsExpired
	 * @throws Domain_User_Exception_Mail_StoryIsSuccess
	 * @throws Domain_User_Exception_Mail_StoryNotFound
	 * @throws Domain_User_Exception_Password_NotFound
	 * @throws Domain_User_Exception_Password_StoryIsExpired
	 * @throws Domain_User_Exception_Password_StoryIsSuccess
	 * @throws Domain_User_Exception_Password_WrongMap
	 * @throws Domain_User_Exception_Security_Mail_Change_CodeStoryNotFound
	 * @throws Domain_User_Exception_Security_Mail_Change_StoryIsExpired
	 * @throws Domain_User_Exception_Security_Mail_Change_StoryIsSuccess
	 * @throws Domain_User_Exception_Security_Mail_Change_StoryNotFound
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \cs_DecryptHasFailed
	 * @throws \queryException
	 */
	public static function resendCode(int $user_id, string $mail_story_key, string $mail_story_type):array {

		// проверяем аутентификация по почте включена в конфиге
		Domain_User_Entity_Auth_Method::assertMethodEnabled(Domain_User_Entity_Auth_Method::METHOD_MAIL);

		switch ($mail_story_type) {

			case Domain_User_Entity_Security_AddMail_Story::ACTION_TYPE:

				$add_mail_story_map = Type_Pack_AddMailStory::doDecrypt($mail_story_key);
				return self::_resendCodeForAddMail($user_id, $add_mail_story_map);

			case Domain_User_Entity_PasswordMail_Story::ACTION_TYPE_RESET_PASSWORD:

				$password_mail_story_map = Type_Pack_PasswordMailStory::doDecrypt($mail_story_key);
				return self::_resendCodeForResetPasswordMail($user_id, $password_mail_story_map);

			case Domain_User_Entity_ChangeMail_Story::ACTION_TYPE:

				$change_mail_story_map = Type_Pack_ChangeMailStory::doDecrypt($mail_story_key);
				return self::_resendCodeForChangeMail($user_id, $change_mail_story_map);

			default:

				throw new ParamException("invalid mail_story_type");
		}
	}

	/**
	 * переотправляем проверочный код для добавления почты
	 *
	 * @return array
	 * @throws Domain_User_Exception_Mail_AlreadyExist
	 * @throws Domain_User_Exception_Mail_CodeResendCountExceeded
	 * @throws Domain_User_Exception_Mail_CodeResendNotAvailable
	 * @throws Domain_User_Exception_Mail_StoryIsExpired
	 * @throws Domain_User_Exception_Mail_StoryIsSuccess
	 * @throws Domain_User_Exception_Mail_StoryNotFound
	 * @throws Domain_User_Exception_UserNotAuthorized
	 * @throws \queryException
	 */
	protected static function _resendCodeForAddMail(int $user_id, string $add_mail_story_map):array {

		// проверяем что почта не установлена
		try {
			$user_security = Gateway_Db_PivotUser_UserSecurity::getOne($user_id);
		} catch (\cs_RowIsEmpty) {
			throw new ParseFatalException("user not found");
		}
		Domain_User_Entity_Mail::assertNotExistMail($user_security);

		// проверяем совпадает ли сценарий с тем что установлен на сервере
		Domain_User_Entity_Mail::assertFullScenario();

		// достаем адрес почты процесса
		try {
			$mail = Type_Pack_AddMailStory::getMail($add_mail_story_map);
		} catch (\cs_UnpackHasFailed) {
			throw new ParseFatalException("mail not found");
		}

		$story = Domain_User_Entity_Security_AddMail_Story::get($add_mail_story_map, $mail)
			->assertUserAuthorized($user_id)
			->assertNotExpire()
			->assertNotSuccess();

		$story_code = Domain_User_Entity_Security_AddMail_CodeStory::get($add_mail_story_map, $mail)->assertNotSuccess();

		try {
			$story_code->assertResendCountNotExceeded($story_code->getAvailableResends())->assertResendIsAvailable($story_code->getNextResend());
		} catch (Domain_User_Exception_Mail_CodeResendCountExceeded $e) {

			$e->setNextAttempt($story->getExpiresAt());
			throw $e;
		}

		$updated_story_story = Domain_User_Action_Security_AddMail_ResendCode::do($add_mail_story_map, $mail, $story_code->getCodeStoryData());

		return [$story, $updated_story_story, self::STAGE_ENTERING_CODE];
	}

	/**
	 * переотправляем проверочный код для сброса пароля
	 *
	 * @return array
	 * @throws Domain_User_Exception_Mail_CodeResendCountExceeded
	 * @throws Domain_User_Exception_Mail_CodeResendNotAvailable
	 * @throws Domain_User_Exception_Mail_NotFound
	 * @throws Domain_User_Exception_Mail_StoryIsExpired
	 * @throws Domain_User_Exception_Password_NotFound
	 * @throws Domain_User_Exception_Password_StoryIsExpired
	 * @throws Domain_User_Exception_Password_StoryIsSuccess
	 * @throws Domain_User_Exception_Password_WrongMap
	 * @throws Domain_User_Exception_UserNotAuthorized
	 * @throws \queryException
	 */
	protected static function _resendCodeForResetPasswordMail(int $user_id, string $password_mail_story_map):array {

		// проверяем что почта не установлена
		try {
			$user_security = Gateway_Db_PivotUser_UserSecurity::getOne($user_id);
		} catch (\cs_RowIsEmpty) {
			throw new ParseFatalException("user not found");
		}
		Domain_User_Entity_Mail::assertAlreadyExistMail($user_security);

		// проверяем совпадает ли сценарий с тем что установлен на сервере
		Domain_User_Entity_Mail::assertFullScenario();

		$story = Domain_User_Entity_PasswordMail_Story::getByMap($password_mail_story_map)
			->assertUserAuthorized($user_id)
			->assertNotExpired()
			->assertNotSuccess();

		$story_code = Domain_User_Entity_PasswordMail_CodeStory::getActive($password_mail_story_map)->assertNotSuccess();

		try {
			$story_code->assertResendCountNotExceeded($story_code->getAvailableResends())->assertResendIsAvailable($story_code->getNextResend());
		} catch (Domain_User_Exception_Mail_CodeResendCountExceeded $e) {

			$e->setNextAttempt($story->getExpiresAt());
			throw $e;
		}

		$updated_story_story = Domain_User_Action_Security_ResetPasswordMail_ResendCode::do($story_code);

		return [$story, $updated_story_story, self::STAGE_ENTERING_CODE];
	}

	/**
	 * переотправляем проверочный код для смены почты
	 *
	 * @return array
	 * @throws Domain_User_Exception_Mail_CodeResendCountExceeded
	 * @throws Domain_User_Exception_Mail_CodeResendNotAvailable
	 * @throws Domain_User_Exception_Mail_NotFound
	 * @throws Domain_User_Exception_Mail_StoryIsExpired
	 * @throws Domain_User_Exception_Security_Mail_Change_CodeStoryNotFound
	 * @throws Domain_User_Exception_Security_Mail_Change_StoryIsExpired
	 * @throws Domain_User_Exception_Security_Mail_Change_StoryIsSuccess
	 * @throws Domain_User_Exception_Security_Mail_Change_StoryNotFound
	 * @throws ParseFatalException
	 */
	protected static function _resendCodeForChangeMail(int $user_id, string $change_mail_story_map):array {

		// проверяем что почта установлена
		try {
			$user_security = Gateway_Db_PivotUser_UserSecurity::getOne($user_id);
		} catch (\cs_RowIsEmpty) {
			throw new ParseFatalException("user not found");
		}
		Domain_User_Entity_Mail::assertAlreadyExistMail($user_security);

		// проверяем совпадает ли сценарий с тем что установлен на сервере
		Domain_User_Entity_Mail::assertFullScenario();

		// получаем story смены почты
		$story      = Domain_User_Entity_ChangeMail_Story::getByMap($change_mail_story_map)->assertUserAuthorized($user_id)->assertNotSuccess()->assertNotExpired();
		$code_story = Domain_User_Entity_ChangeMail_CodeStory::getActive($change_mail_story_map, $user_security->mail);

		// выполняем проверки
		try {

			$code_story->assertResendCountNotExceeded($code_story->getAvailableResends())->assertResendIsAvailable($code_story->getNextResend());
		} catch (Domain_User_Exception_Mail_CodeResendCountExceeded $e) {

			$e->setNextAttempt($story->getExpiresAt());
			throw $e;
		}

		$updated_story_story = Domain_User_Action_Security_ChangeMail_ResendCode::do($code_story);

		return [$story, $updated_story_story, self::STAGE_ENTERING_CODE];
	}

	##########################################################
	# region - смена почты
	##########################################################

	/**
	 * Метод старта процесса смены почты
	 *
	 * @throws Domain_User_Exception_Mail_NotFound
	 * @throws Domain_User_Exception_Security_UserWasRegisteredBySso
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function change(int $user_id, string $session_uniq):array {

		// проверяем аутентификация по почте включена в конфиге
		Domain_User_Entity_Auth_Method::assertMethodEnabled(Domain_User_Entity_Auth_Method::METHOD_MAIL);

		// проверяем что пользователь не зарегистрирован через SSO
		Domain_User_Entity_Mail::assertUserWasNotRegisteredBySso($user_id);

		// получаем почту пользователя и проверяем что она действительно есть
		try {
			$user_security = Gateway_Db_PivotUser_UserSecurity::getOne($user_id);
		} catch (\cs_RowIsEmpty) {
			throw new ParseFatalException("user not found");
		}
		Domain_User_Entity_Mail::assertAlreadyExistMail($user_security);

		// получаем тип сценария
		$scenario = Domain_User_Entity_Auth_Config::isMailAuthorization2FAEnabled() ? self::SCENARIO_FULL_CHANGE : self::SCENARIO_SHORT_CHANGE;

		// создаем новый процесс
		$story    = Domain_User_Entity_ChangeMail_Story::createNewStory($user_id, $session_uniq, Domain_User_Entity_ChangeMail_Story::STAGE_FIRST);
		$story_id = Gateway_Db_PivotMail_MailChangeStory::insert($story->getStoryData());
		$story    = Domain_User_Entity_ChangeMail_Story::updateStory(
			$story->getStoryData(), ["change_mail_story_id" => $story_id]
		);
		$story->storeInSessionCache($session_uniq);

		// получаем code_story
		$code_story = match ($scenario) {
			self::SCENARIO_FULL_CHANGE  => Domain_User_Action_Security_ChangeMail_Begin::doFull($story->getStoryMap(), $session_uniq, $user_security->mail),
			self::SCENARIO_SHORT_CHANGE => Domain_User_Action_Security_ChangeMail_Begin::doShort($story->getStoryMap(), $session_uniq, $user_security->mail),
			default                     => throw new ParseFatalException("parse error scenario change mail"),
		};

		return [$story, $code_story, $scenario];
	}

	/**
	 * Метод добавление нового адреса почты в процессе смены почты (по короткому сценарию)
	 *
	 * @throws Domain_User_Exception_AuthMethodDisabled
	 * @throws Domain_User_Exception_AuthStory_Mail_DomainNotAllowed
	 * @throws Domain_User_Exception_Mail_Binding
	 * @throws Domain_User_Exception_Mail_IsTaken
	 * @throws Domain_User_Exception_Mail_NotFound
	 * @throws Domain_User_Exception_Mail_ScenarioNotAllowed
	 * @throws Domain_User_Exception_Security_Mail_Change_CodeStoryNotFound
	 * @throws Domain_User_Exception_Security_Mail_Change_StoryIsExpired
	 * @throws Domain_User_Exception_Security_Mail_Change_StoryIsSuccess
	 * @throws Domain_User_Exception_Security_Mail_Change_StoryNotFound
	 * @throws Domain_User_Exception_Security_Mail_Change_WrongStage
	 * @throws InvalidMail
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function setOnShortChange(int $user_id, string $session_uniq, string $change_mail_story_map, string $mail):string {

		// проверяем аутентификация по почте включена в конфиге
		Domain_User_Entity_Auth_Method::assertMethodEnabled(Domain_User_Entity_Auth_Method::METHOD_MAIL);

		// проверяем введенные mail на корректность
		$mail = (new \BaseFrame\System\Mail($mail))->mail();

		// проверяем, что разрешен ли для аутентификации данный домен почты
		Domain_User_Action_Auth_Mail::assertAllowRegistration($mail);

		// получаем почту пользователя и проверяем что она действительно есть
		try {
			$user_security = Gateway_Db_PivotUser_UserSecurity::getOne($user_id);
		} catch (\cs_RowIsEmpty) {
			throw new ParseFatalException("user not found");
		}
		Domain_User_Entity_Mail::assertAlreadyExistMail($user_security);

		// проверяем что данная почта никем не занята
		Domain_User_Entity_Mail::assertMailNotTakenAnotherUser($mail, $user_id);

		// проверяем совпадает ли сценарий с тем что установлен на сервере
		Domain_User_Entity_Mail::assertShortScenario();

		// получаем story смены почты
		$story      = Domain_User_Entity_ChangeMail_Story::getByMap($change_mail_story_map)
			->assertUserAuthorized($user_id)
			->assertNotSuccess()
			->assertNotExpired()
			->assertFirstStage();
		$code_story = Domain_User_Entity_ChangeMail_CodeStory::getActive($change_mail_story_map, $user_security->mail);

		// если не совпал user_id
		if ($story->getStoryData()->user_id !== $user_id) {
			throw new Domain_User_Exception_UserNotAuthorized();
		}

		// обновляем почту
		Domain_User_Action_Security_ChangeMail_Update::do($story, $mail);

		// обрабатываем успех и чистим кеши
		$story->handleSuccess($story->getStoryData())->deleteSessionCache($session_uniq);
		$code_story->handleSuccessCode($code_story->getCodeStoryData())->deleteSessionCache($session_uniq, Domain_User_Entity_ChangeMail_Story::STAGE_SECOND);
		return $mail;
	}

	/**
	 * Метод подтверждение смены почты кодом на прошлую почту (по полному сценарию)
	 *
	 * @throws Domain_User_Exception_AuthMethodDisabled
	 * @throws Domain_User_Exception_Mail_NotFound
	 * @throws Domain_User_Exception_Security_Mail_Change_CodeErrorCountExceeded
	 * @throws Domain_User_Exception_Security_Mail_Change_CodeStoryNotFound
	 * @throws Domain_User_Exception_Security_Mail_Change_StoryIsExpired
	 * @throws Domain_User_Exception_Security_Mail_Change_StoryIsSuccess
	 * @throws Domain_User_Exception_Security_Mail_Change_StoryNotFound
	 * @throws Domain_User_Exception_Security_Mail_Change_WrongStage
	 * @throws ParseFatalException
	 * @throws cs_WrongCode
	 */
	public static function confirmOldByCodeOnFullChange(int $user_id, string $session_uniq, string $change_mail_story_map, string $code):void {

		// проверяем аутентификация по почте включена в конфиге
		Domain_User_Entity_Auth_Method::assertMethodEnabled(Domain_User_Entity_Auth_Method::METHOD_MAIL);

		// получаем почту пользователя и проверяем что она действительно есть
		try {
			$user_security = Gateway_Db_PivotUser_UserSecurity::getOne($user_id);
		} catch (\cs_RowIsEmpty) {
			throw new ParseFatalException("user not found");
		}
		Domain_User_Entity_Mail::assertAlreadyExistMail($user_security);

		// проверяем совпадает ли сценарий с тем что установлен на сервере
		Domain_User_Entity_Mail::assertFullScenario();

		// получаем story смены почты
		$story = Domain_User_Entity_ChangeMail_Story::getByMap($change_mail_story_map)
			->assertUserAuthorized($user_id)
			->assertNotSuccess()
			->assertNotExpired()
			->assertFirstStage();

		$code_story = $story->getActiveCodeStoryEntity($user_security->mail);

		try {

			$code_story->assertCodeErrorCountLimitNotExceeded()->assertEqualCode($code);
		} catch (Domain_User_Exception_Security_Mail_Change_CodeErrorCountExceeded $e) {

			// выкидываем ошибку о том, что смена почты временно заблокирована (из-за превышения кол-ва ошибок)
			$e->setNextAttempt($story->getExpiresAt());
			throw $e;
		} catch (cs_WrongCode $e) {

			// записываем в историю ошибку подтверждения и обновляем кэш
			$story->handleWrongCode($story->getStoryData());
			$code_story->handleWrongCode($code_story->getCodeStoryData());

			$story->storeInSessionCache($session_uniq);

			throw $e;
		}

		// обновляем попытку, указываем что код был подтвержден
		$story->handleFirstStage($story->getStoryData())->storeInSessionCache($session_uniq);
		$code_story->handleSuccessCode($code_story->getCodeStoryData());
	}

	/**
	 * Метод добавление нового адреса почты в процессе смены почты (по полному сценарию)
	 *
	 * @throws Domain_User_Exception_AuthStory_Mail_DomainNotAllowed
	 * @throws Domain_User_Exception_Mail_NotFound
	 * @throws Domain_User_Exception_Security_Mail_Change_CodeStoryIsNotConfirmStage
	 * @throws Domain_User_Exception_Security_Mail_Change_CodeStoryNotFound
	 * @throws Domain_User_Exception_Security_Mail_Change_StoryIsExpired
	 * @throws Domain_User_Exception_Security_Mail_Change_StoryIsSuccess
	 * @throws Domain_User_Exception_Security_Mail_Change_StoryNotFound
	 * @throws Domain_User_Exception_Security_Mail_Change_WrongStage
	 * @throws InvalidMail
	 * @throws ParseFatalException
	 * @throws \queryException
	 * @throws Domain_User_Exception_Mail_IsTaken
	 */
	public static function setOnFullChange(int $user_id, string $session_uniq, string $change_mail_story_map, string $mail):array {

		// проверяем аутентификация по почте включена в конфиге
		Domain_User_Entity_Auth_Method::assertMethodEnabled(Domain_User_Entity_Auth_Method::METHOD_MAIL);

		// получаем почту пользователя и проверяем что она действительно есть
		try {
			$user_security = Gateway_Db_PivotUser_UserSecurity::getOne($user_id);
		} catch (\cs_RowIsEmpty) {
			throw new ParseFatalException("user not found");
		}
		Domain_User_Entity_Mail::assertAlreadyExistMail($user_security);

		// проверяем совпадает ли сценарий с тем что установлен на сервере
		Domain_User_Entity_Mail::assertFullScenario();

		// проверяем введенные mail на корректность
		$mail = (new \BaseFrame\System\Mail($mail))->mail();

		// проверяем, что разрешен ли для аутентификации данный домен почты
		Domain_User_Action_Auth_Mail::assertAllowRegistration($mail);

		// проверяем что данная почта не занята другим пользователем
		Domain_User_Entity_Mail::assertMailNotTakenAnotherUser($mail, $user_id);

		// получаем story смены почты
		$story = Domain_User_Entity_ChangeMail_Story::getByMap($change_mail_story_map)
			->assertUserAuthorized($user_id)
			->assertNotSuccess()
			->assertNotExpired()
			->assertSecondStage();

		$code_story = [];
		try {

			// получаем текущую story
			$code_story = $story->getActiveCodeStoryEntity($user_security->mail)->assertCodeErrorCountLimitNotExceeded();

			// проверяем что меняем на новую почту
			$code_story->assertNotEqualMail($mail);

			// проверяем блокировку
			Type_Antispam_User::throwIfBlocked($user_id, Type_Antispam_User::MAIL_CHANGE_CONFIRM);

			return Domain_User_Action_Security_ChangeMail_SetOnFull::do($story, $code_story, $session_uniq, $mail);
		} catch (Domain_User_Exception_Security_Mail_Change_CodeStoryNotFound) {

			return Domain_User_Action_Security_ChangeMail_SetOnFull::do($story, $code_story, $session_uniq, $mail);
		} catch (Domain_User_Exception_Security_Mail_Change_SameMail) {

			// если почту ввели второй раз подряд, то отправляем уже существующие данные (повторно отправлять код не требуется)
			// кейс в возвратом назад назад на ввод новый почты с клиента
			$code_story_data = $code_story->getStoryData();
			if ($code_story_data->stage === Domain_User_Entity_ChangeMail_CodeStory::STAGE_START) {
				return [$story, $code_story, self::SCENARIO_FULL_CHANGE];
			}

			return Domain_User_Action_Security_ChangeMail_SetOnFull::do($story, $code_story, $session_uniq, $mail);
		} catch (Domain_User_Exception_Security_Mail_Change_CodeErrorCountExceeded $e) {

			$e->setNextAttempt($story->getExpiresAt());
			throw $e;
		}
	}

	/**
	 * Метод подтверждение смены почты кодом на новую почту
	 *
	 * @throws Domain_User_Exception_AuthMethodDisabled
	 * @throws Domain_User_Exception_Mail_Binding
	 * @throws Domain_User_Exception_Mail_IsTaken
	 * @throws Domain_User_Exception_Mail_NotFound
	 * @throws Domain_User_Exception_Security_Mail_Change_CodeErrorCountExceeded
	 * @throws Domain_User_Exception_Security_Mail_Change_StoryIsExpired
	 * @throws Domain_User_Exception_Security_Mail_Change_StoryIsSuccess
	 * @throws Domain_User_Exception_Security_Mail_Change_StoryNotFound
	 * @throws Domain_User_Exception_Security_Mail_Change_WrongStage
	 * @throws InvalidMail
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws cs_CacheIsEmpty
	 * @throws cs_WrongCode
	 */
	public static function confirmNewByCodeOnFullChange(int $user_id, string $session_uniq, string $change_mail_story_map, string $code):string {

		// проверяем аутентификация по почте включена в конфиге
		Domain_User_Entity_Auth_Method::assertMethodEnabled(Domain_User_Entity_Auth_Method::METHOD_MAIL);

		// проверяем совпадает ли сценарий с тем что установлен на сервере
		Domain_User_Entity_Mail::assertFullScenario();

		// получаем story смены почты
		$story      = Domain_User_Entity_ChangeMail_Story::getByMap($change_mail_story_map)
			->assertUserAuthorized($user_id)
			->assertNotSuccess()
			->assertNotExpired()
			->assertSecondStage();
		$code_story = Domain_User_Entity_ChangeMail_CodeStory::getFromSessionCache($session_uniq, Domain_User_Entity_ChangeMail_Story::STAGE_SECOND);

		// проверяем что данная почта не занята другим пользователем
		Domain_User_Entity_Mail::assertMailNotTakenAnotherUser($code_story->getCodeStoryData()->mail_new, $user_id);

		try {
			$code_story->assertCodeErrorCountLimitNotExceeded()->assertEqualCode($code);
		} catch (Domain_User_Exception_Security_Mail_Change_CodeErrorCountExceeded $e) {

			// выкидываем ошибку о том, что смена почты временно заблокирована (из-за превышения кол-ва ошибок)
			$e->setNextAttempt($story->getExpiresAt());
			throw $e;
		} catch (cs_WrongCode $e) {

			// записываем в историю ошибку подтверждения и обновляем кэш
			$story->handleWrongCode($story->getStoryData());
			$code_story->handleWrongCode($code_story->getCodeStoryData());

			$story->storeInSessionCache($session_uniq);
			$code_story->storeInSessionCache($session_uniq, Domain_User_Entity_ChangeMail_Story::STAGE_SECOND);

			throw $e;
		}

		// обновляем почту
		Domain_User_Action_Security_ChangeMail_Update::do($story, $code_story->getCodeStoryData()->mail_new);

		// обрабатываем успех и чистим кеши
		$story->handleSuccess($story->getStoryData())->deleteSessionCache($session_uniq);
		$code_story->handleSuccessCode($code_story->getCodeStoryData())->deleteSessionCache($session_uniq, Domain_User_Entity_ChangeMail_Story::STAGE_SECOND);

		return $code_story->getCodeStoryData()->mail_new;
	}

	/**
	 * Метод подтверждения пароля
	 *
	 * @param int    $user_id
	 * @param string $password
	 * @param string $confirm_mail_password_story_map
	 *
	 * @throws Domain_User_Exception_Confirmation_Mail_ErrorCountExceeded
	 * @throws Domain_User_Exception_Confirmation_Mail_InvalidMailPasswordStoryKey
	 * @throws Domain_User_Exception_Confirmation_Mail_IsConfirmed
	 * @throws Domain_User_Exception_Confirmation_Mail_IsExpired
	 * @throws Domain_User_Exception_Confirmation_Mail_IsNotActive
	 * @throws Domain_User_Exception_Confirmation_Mail_WrongPassword
	 * @throws Domain_User_Exception_Mail_NotFound
	 * @throws ParseFatalException
	 * @throws \cs_RowIsEmpty
	 */
	public static function confirmMailPasswordStory(int $user_id, string $password, string $confirm_mail_password_story_map):void {

		// проверяем аутентификация по почте включена в конфиге
		Domain_User_Entity_Auth_Method::assertMethodEnabled(Domain_User_Entity_Auth_Method::METHOD_MAIL);

		// проверяем наличие почты у пользователя
		$user_security = Gateway_Db_PivotUser_UserSecurity::getOne($user_id);
		Domain_User_Entity_Mail::assertAlreadyExistMail($user_security);

		// получаем запись аутентификации
		$story = Domain_User_Entity_Confirmation_Mail_Story::getByMap($confirm_mail_password_story_map);

		// делаем общие проверки
		$story->assertNotExpired()
			->assertErrorCountLimitNotExceeded()
			->assertActive()
			->assertIsNotConfirmed();

		$mail_uniq_obj = Domain_User_Entity_Mail::get($user_security->mail);

		if (!Domain_User_Entity_Password::isEqual($password, $mail_uniq_obj->password_hash)) {

			$story->handleWrongPassword();
			throw new Domain_User_Exception_Confirmation_Mail_WrongPassword("wrong password");
		}

		// обновляем попытку, указываем что код был подтвержден
		$story->handleSuccessPassword();
	}
}