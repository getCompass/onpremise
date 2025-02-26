<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Сценарии для работы с аутентификаций через почту на веб-сайте on-premise решений.
 */
class Domain_User_Scenario_OnPremiseWeb_Auth_Mail {

	public const SCENARIO_SHORT_CONFIRM = "short_confirm";
	public const SCENARIO_FULL_CONFIRM  = "full_confirm";

	/**
	 * получаем сценарий аутентификации (короткий/полный)
	 *
	 * @return string
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function resolveScenario(int $auth_type):string {

		if ($auth_type === Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_RESET_PASSWORD_BY_MAIL) {
			return self::SCENARIO_FULL_CONFIRM;
		}

		return Domain_User_Action_Auth_Mail::is2FAEnabled($auth_type) ? self::SCENARIO_FULL_CONFIRM : self::SCENARIO_SHORT_CONFIRM;
	}

	/**
	 * запускаем аутентификацию через почту
	 *
	 * @return array
	 * @throws CaseException
	 * @throws Domain_Link_Exception_TemporaryUnavailable
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\InvalidMail
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \busException
	 * @throws \userAccessException
	 * @throws cs_IncorrectJoinLink
	 * @throws cs_JoinLinkIsNotActive
	 * @throws cs_JoinLinkIsUsed
	 * @throws cs_JoinLinkNotFound
	 * @throws cs_PhoneNumberIsNotEqual
	 * @throws cs_PlatformNotFound
	 * @throws cs_RecaptchaIsRequired
	 * @throws cs_UserAlreadyInCompany
	 * @throws cs_UserAlreadyLoggedIn
	 * @throws cs_UserNotFound
	 * @throws cs_WrongRecaptcha
	 * @long
	 */
	public static function begin(int $user_id, string $mail, string|false $grecaptcha_response, string|false $join_link):array {

		// проверяем, что нет текущей активной сессии
		Domain_User_Entity_Validator::assertNotLoggedIn($user_id);

		// форматируем почту
		$mail = (new \BaseFrame\System\Mail($mail))->mail();

		// получаем user_id по почте
		$existing_user_id = Domain_User_Action_Auth_Mail::resolveUser($mail);

		// выбрасываем исключение, если пытаются начать авторизацию под заблокированным пользователем
		$existing_user_id > 0 && Domain_User_Entity_User::throwIfUserDisabled($existing_user_id);

		// если не нашли пользователя
		if ($existing_user_id === 0) {

			// проверяем, что разрешена регистрация по этому домену почты
			Domain_User_Action_Auth_Mail::assertAllowRegistration($mail);

			// если пользователь не зарегистрирован и если ссылка не передана, то сразу завершаем процесс
			$join_link === false && throw new Domain_User_Exception_AuthStory_RegistrationWithoutInvite();
		}

		// проверяем что пользователь не зарегистрирован через SSO
		Domain_User_Entity_Mail::assertUserWasNotRegisteredBySso($existing_user_id);

		// валидируем ссылку-приглашение, если она передана
		$validation_result = Domain_Link_Action_OnPremiseWeb::validateJoinLinkIfNeeded($join_link, $existing_user_id);

		// проверяем требуется ли разгадывание капчи / проверяем капчу если уже прислана / сообщаем требуется ли ввод капчи на след. этапе
		Domain_User_Entity_Antispam_Auth::checkIpAddressBlocksBeforeStartAuth($grecaptcha_response, true);

		try {

			// получаем значение из кеша, если есть, иначе дальше начнем регистрацию/логин
			$story = Domain_User_Entity_AuthStory::getFromSessionCache($mail)
				->assertNotExpired()
				->assertAuthParameter($mail)
				->assertType([Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_REGISTER_BY_MAIL, Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_LOGIN_BY_MAIL]);
		} catch (cs_CacheIsEmpty|cs_AuthIsExpired|Domain_User_Exception_AuthStory_AuthParameterNotEqual|Domain_User_Exception_AuthStory_TypeMismatch) {

			if ($existing_user_id === 0) {
				$story = Domain_User_Action_Auth_Mail::beginRegistration($mail);
			} else {
				$story = Domain_User_Action_Auth_Mail::beginLogin($existing_user_id, $mail);
			}
		}

		if (isset($validation_result)) {

			// сохраняем ссылку-приглашение если есть в кэш
			// это нам пригодится в дальнейшем, чтобы проверить переданные от клиента данные
			ShardingGateway::cache()->set(
				$story->getAuthInfo()->auth_map,
				$validation_result->invite_link_rel->join_link_uniq
			);
		}

		return [
			$story->getAuthInfo(),
			$validation_result ?? false,
			self::resolveScenario($story->getType()),
		];
	}

	/**
	 * @return array
	 * @throws CaseException
	 * @throws Domain_User_Exception_AuthStory_Mail_ShortConfirmScenarioNotAllowed
	 * @throws Domain_User_Exception_Mail_NotFound
	 * @throws Domain_User_Exception_Password_Incorrect
	 * @throws Domain_User_Exception_Password_Mismatch
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \userAccessException
	 * @throws cs_AuthAlreadyFinished
	 * @throws cs_AuthIsBlocked
	 * @throws cs_AuthIsExpired
	 * @throws cs_IncorrectSaltVersion
	 * @throws cs_PlatformNotFound
	 * @throws cs_RecaptchaIsRequired
	 * @throws cs_UserAlreadyLoggedIn
	 * @throws cs_WrongAuthKey
	 * @throws cs_WrongRecaptcha
	 * @long
	 */
	public static function confirmShortAuthPassword(int $user_id, string $auth_map, string $password, string|false $grecaptcha_response, string|false $join_link_uniq):array {

		// проверяем, что нет текущей активной сессии
		Domain_User_Entity_Validator::assertNotLoggedIn($user_id);
		Domain_User_Entity_Password::throwIfIncorrect($password);

		// получаем запись аутентификации
		$story = Domain_User_Entity_AuthStory::getByMap($auth_map);

		try {

			// делаем общие для всех типов аутентификаций проверки
			$story->assertNotExpired()
				->assertNotFinishedYet()
				->assertType([Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_LOGIN_BY_MAIL, Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_REGISTER_BY_MAIL]);

			// выбрасываем исключение, если это действие недоступно
			Domain_User_Action_Auth_Mail::assertShortConfirmScenarioAllowed($story->getType());

			// проверяем кол-во неверного ввода пароля
			Domain_User_Action_Auth_Mail::checkInvalidConfirmPasswordLimit($story);

			// проверяем, нужно ли запросить капчу на ввод пароля
			Domain_User_Action_Auth_Mail::checkCaptchaOnEnteringPassword($story, $grecaptcha_response);

			// проверяем пароль
			Domain_User_Action_Auth_Mail::verifyPasswordOnShortScenario($story, $password);
		} catch (Domain_User_Exception_Password_Mismatch) {

			// фиксируем неправильный ввод пароля
			$story = Domain_User_Action_Auth_Mail::onWrongPassword($story);

			// увеличиваем кол-во неверного ввода пароля
			$available_password_entering_attempt_count = Domain_User_Action_Auth_Mail::incInvalidConfirmPasswordLimit($story);

			throw new Domain_User_Exception_AuthStory_WrongPassword($available_password_entering_attempt_count);
		}

		// в зависимости от типа аутентификации регистрируем и/или авторизуем пользователя
		/**
		 * @noinspection PhpUnusedLocalVariableInspection
		 * @var Struct_Integration_Notifier_Response_OnUserRegistered|null $integration_response
		 */
		[$user_id, $invite_accept_info, $integration_response] = $story->isNeedToCreateUser()
			? static::_confirmNotRegisteredUserAuthentication($story, Domain_User_Entity_Password::makeHash($password), $join_link_uniq)
			: static::_confirmRegisteredUserAuthentication($story, $join_link_uniq);

		// чистим кэш попыток аутентификации
		$story->clearAuthCache();

		// выдаем пользовательскую сессию
		// !!! в этом методе в сессию передаём тип авторизации через web-сайт
		Type_Session_Main::doLoginSession($user_id, Domain_User_Entity_SessionExtra::ONPREMISE_WEB_LOGIN_TYPE);

		// !!! для генерации токена уже передаём тип авторизации из auth_story
		$login_type = Domain_User_Entity_SessionExtra::getLoginTypeByAuthType($story->getType());

		if (!$story->isNeedToCreateUser()) {

			$user_agent  = getUa();
			$device_name = Type_Api_Platform::getDeviceName($user_agent);
			$app_version = Type_Api_Platform::getVersion($user_agent);
			Domain_User_Action_Security_Device_OnSuccessLogin::do($user_id, $login_type, $device_name, $app_version, ONPREMISE_VERSION);
		}

		// устанавливаем, что аутентификация прошла успешно
		$story->handleSuccess($user_id);
		Gateway_Db_PivotHistoryLogs_UserAuthHistory::insert($story->getAuthMap(), $user_id, Domain_User_Entity_AuthStory::HISTORY_AUTH_STATUS_SUCCESS, time(), 0);

		[$token,] = Domain_Solution_Action_GenerateAuthenticationToken::exec($user_id, $join_link_uniq, $login_type);
		return [
			$token,
			Type_User_Main::isEmptyProfile($user_id),
			!is_null($integration_response) ? $integration_response->action_list : [],
		];
	}

	/**
	 * Выполняет кусок логики подтверждения аутентификации для уже зарегистрированного пользователя.
	 */
	protected static function _confirmRegisteredUserAuthentication(Domain_User_Entity_AuthStory $story, string|false $join_link_uniq):array {

		$user_id = $story->getUserId();

		if ($join_link_uniq !== false) {

			// получаем данные ссылки-приглашения из базы
			$cached_join_link_uniq = ShardingGateway::cache()->get($story->getAuthMap());

			if ($cached_join_link_uniq !== false) {

				try {

					// получаем приглашение, оно должно существовать,
					// поскольку данные были получены и сверены из кэша
					$join_link_rel_row = Gateway_Db_PivotData_CompanyJoinLinkRel::get($join_link_uniq);
				} catch (\cs_RowIsEmpty) {
					throw new ReturnFatalException("invite not found");
				}

				try {

					$validation_result  = Domain_Link_Entity_Link::validateForUser($user_id, $join_link_rel_row);
					$invite_accept_info = [
						$join_link_rel_row,
						Gateway_Bus_PivotCache::getUserInfo($user_id),
						$validation_result,
					];
				} catch (\Exception) {
					// ничего не делаем, стоит тут как-то ошибку выкинуть, но пока ничего не делаем
				}
			}
		}

		// добавляем в историю, что пользователь залогинился
		Domain_User_Entity_UserActionComment::addUserLoginAction($user_id, $story->getType(), $story->getAuthMailHandler()->getMail(), getDeviceId(), getUa());

		return [$user_id, $invite_accept_info ?? false, null];
	}

	/**
	 * Выполняет кусок логики для создания нового пользователя и подтверждения аутентификации.
	 * @long
	 */
	protected static function _confirmNotRegisteredUserAuthentication(Domain_User_Entity_AuthStory $story, string $password_hash, string|false $join_link_uniq):array {

		try {

			// проверяем, может почта уже зарегистрирована
			$user_mail = Domain_User_Entity_Mail::get($story->getAuthMailHandler()->getMail());

			// если зарегистрирована, то просим клиентское приложение начать аутентификацию заново
			if ($user_mail->user_id > 0) {
				throw new CaseException(Onpremiseweb_Auth_Mail::ECODE_AUTH_NEED_RESTART, "auth restart needed");
			}
		} catch (Domain_User_Exception_Mail_NotFound) {

			// это нормально, просто проверка, что почта не занята
		}

		// без ссылки не создаем нового пользователя
		if ($join_link_uniq === false) {
			throw new CaseException(1000, "registration is not allowed without invite");
		}

		// получаем кэшированное приглашение, чтобы убедить, что с клиента не пришло что-то другое
		$cached_join_link_uniq = ShardingGateway::cache()->get($story->getAuthMap());

		// проверяем, что приглашения совпадают
		if ($cached_join_link_uniq !== $join_link_uniq) {
			throw new CaseException(1000, "invite data was changed during registration");
		}

		try {

			// получаем приглашение, оно должно существовать,
			// поскольку данные были получены и сверены из кэша
			$join_link_rel_row = Gateway_Db_PivotData_CompanyJoinLinkRel::get($join_link_uniq);
		} catch (\cs_RowIsEmpty) {
			throw new ReturnFatalException("invite not found");
		}

		// проверим, что наше приглашение подходит для создание пользвоателя
		$validation_result = Domain_Link_Entity_Link::validateBeforeRegistration($join_link_rel_row);

		// регистрируем и отмечаем в истории событие
		$user                 = Domain_User_Action_Create_Human::do("", $story->getAuthMailHandler()->getMail(), $password_hash, getUa(), getIp(), "", "", [], 0, 0);
		$integration_response = Domain_Integration_Entity_Notifier::onUserRegistered(new Struct_Integration_Notifier_Request_OnUserRegistered(
			user_id: $user->user_id,
			auth_method: Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_REGISTER_BY_MAIL,
			registered_by_phone_number: "",
			registered_by_mail: $story->getAuthMailHandler()->getMail(),
			join_link_uniq: $validation_result->invite_link_rel->join_link_uniq,
		));
		Type_Phphooker_Main::sendUserAccountLog($user->user_id, Type_User_Analytics::REGISTERED);

		return [$user->user_id, [$join_link_rel_row, $user, $validation_result], $integration_response];
	}

	/**
	 * подтверждаем паролем попытку аутентификации по полному сценарию
	 *
	 * @throws Domain_User_Exception_AuthStory_Mail_FullConfirmScenarioNotAllowed
	 * @throws Domain_User_Exception_AuthStory_WrongPassword
	 * @throws Domain_User_Exception_Mail_NotFound
	 * @throws Domain_User_Exception_Password_Incorrect
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \parseException
	 * @throws cs_AuthAlreadyFinished
	 * @throws cs_AuthIsBlocked
	 * @throws cs_AuthIsExpired
	 * @throws cs_PlatformNotFound
	 * @throws cs_RecaptchaIsRequired
	 * @throws cs_UserAlreadyLoggedIn
	 * @throws cs_WrongAuthKey
	 * @throws cs_WrongRecaptcha
	 */
	public static function confirmFullAuthPassword(int $user_id, string $auth_map, string $password, string|false $grecaptcha_response):Struct_User_Auth_Info {

		// проверяем, что нет текущей активной сессии
		Domain_User_Entity_Validator::assertNotLoggedIn($user_id);
		Domain_User_Entity_Password::throwIfIncorrect($password);

		// получаем запись аутентификации
		$story = Domain_User_Entity_AuthStory::getByMap($auth_map);

		try {

			// делаем общие для всех типов аутентификаций проверки
			$story->assertNotExpired()
				->assertNotFinishedYet()
				->assertType([Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_LOGIN_BY_MAIL, Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_REGISTER_BY_MAIL]);

			// выбрасываем исключение, если это действие недоступно
			Domain_User_Action_Auth_Mail::assertFullConfirmScenarioAllowed($story->getType());

			// проверяем кол-во неверного ввода пароля
			Domain_User_Action_Auth_Mail::checkInvalidConfirmPasswordLimit($story);

			// проверяем, нужно ли запросить капчу на ввод пароля
			Domain_User_Action_Auth_Mail::checkCaptchaOnEnteringPassword($story, $grecaptcha_response);

			// проверяем пароль и отправляем на следующий этап, если он корректен
			$story = Domain_User_Action_Auth_Mail::verifyPasswordOnFullScenario($story, $password);
		} catch (Domain_User_Exception_Password_Mismatch) {

			// фиксируем неправильный ввод пароля
			$story = Domain_User_Action_Auth_Mail::onWrongPassword($story);

			// увеличиваем кол-во неверного ввода пароля
			$available_password_entering_attempt_count = Domain_User_Action_Auth_Mail::incInvalidConfirmPasswordLimit($story);

			throw new Domain_User_Exception_AuthStory_WrongPassword($available_password_entering_attempt_count);
		}

		return $story->getAuthInfo();
	}

	/**
	 * Подтверждаем кодом аутентификацию через почту по полному сценарию
	 *
	 * @return array
	 * @throws CaseException
	 * @throws Domain_User_Exception_AuthStory_StageNotAllowed
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \userAccessException
	 * @throws cs_AuthAlreadyFinished
	 * @throws cs_AuthIsBlocked
	 * @throws cs_AuthIsExpired
	 * @throws cs_CacheIsEmpty
	 * @throws cs_IncorrectSaltVersion
	 * @throws cs_InvalidConfirmCode
	 * @throws cs_UserAlreadyLoggedIn
	 * @throws cs_WrongAuthKey
	 * @throws cs_WrongCode
	 * @long
	 */
	public static function confirmFullAuthCode(int $user_id, string $auth_map, string $code, string|false $join_link_uniq):array {

		// проверяем, что нет текущей активной сессии
		Domain_User_Entity_Validator::assertNotLoggedIn($user_id);
		Domain_User_Entity_Validator::assertValidConfirmCode($code);

		// получаем запись аутентификации
		$story = Domain_User_Entity_AuthStory::getByMap($auth_map);

		// делаем общие для всех типов аутентификаций проверки
		$story->assertNotExpired()
			->assertNotFinishedYet()
			->assertType([Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_LOGIN_BY_MAIL, Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_REGISTER_BY_MAIL]);

		// выбрасываем исключение, если это действие недоступно
		Domain_User_Action_Auth_Mail::assertFullConfirmScenarioAllowed($story->getType());

		try {

			// делаем проверки свойственные аутентификации по почте
			self::_throwIfCodeErrorCountLimitExceeded($story);
			$story->getAuthMailHandler()->assertAccessEnteringCodeStage()->assertEqualCode($code);
		} catch (cs_WrongCode $e) {

			// записываем в историю ошибку подтверждения и обновляем кэш
			$story->getAuthMailHandler()->handleWrongCode();
			$story->storeInSessionCache();

			// если получен неверный код, и достигнут лимит, то кидаем ошибку лимита
			self::_throwIfCodeErrorCountLimitExceeded($story);

			throw $e;
		}

		// получаем параметры аутентификации
		[$confirm_code, $password] = Domain_User_Entity_CachedConfirmCode::getMailAuthFullScenarioParams();

		// в зависимости от типа аутентификации регистрируем и/или авторизуем пользователя
		/**
		 * @noinspection PhpUnusedLocalVariableInspection
		 * @var Struct_Integration_Notifier_Response_OnUserRegistered|null $integration_response
		 */
		[$user_id, $invite_accept_info, $integration_response] = $story->isNeedToCreateUser()
			? static::_confirmNotRegisteredUserAuthentication($story, Domain_User_Entity_Password::makeHash($password), $join_link_uniq)
			: static::_confirmRegisteredUserAuthentication($story, $join_link_uniq);

		// чистим кэш попыток аутентификации
		$story->clearAuthCache();

		// выдаем пользовательскую сессию
		// !!! в этом методе в сессию передаём тип авторизации через web-сайт
		Type_Session_Main::doLoginSession($user_id, Domain_User_Entity_SessionExtra::ONPREMISE_WEB_LOGIN_TYPE);

		// !!! для генерации токена уже передаём тип авторизации из auth_story
		$login_type = Domain_User_Entity_SessionExtra::getLoginTypeByAuthType($story->getType());

		if (!$story->isNeedToCreateUser()) {

			$user_agent  = getUa();
			$device_name = Type_Api_Platform::getDeviceName($user_agent);
			$app_version = Type_Api_Platform::getVersion($user_agent);
			Domain_User_Action_Security_Device_OnSuccessLogin::do($user_id, $login_type, $device_name, $app_version, ONPREMISE_VERSION);
		}

		// устанавливаем, что аутентификация прошла успешно
		$story->handleSuccess($user_id, [
			"has_code" => 1,
		]);
		Gateway_Db_PivotHistoryLogs_UserAuthHistory::insert($story->getAuthMap(), $user_id, Domain_User_Entity_AuthStory::HISTORY_AUTH_STATUS_SUCCESS, time(), 0);

		[$token,] = Domain_Solution_Action_GenerateAuthenticationToken::exec($user_id, $join_link_uniq, $login_type);
		return [
			$token,
			Type_User_Main::isEmptyProfile($user_id),
			!is_null($integration_response) ? $integration_response->action_list : [],
		];
	}

	/**
	 * Переотправляем код
	 *
	 * @return Struct_User_Auth_Info
	 * @throws Domain_User_Exception_AuthStory_Mail_FullConfirmScenarioNotAllowed
	 * @throws Domain_User_Exception_AuthStory_StageNotAllowed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \parseException
	 * @throws cs_AuthAlreadyFinished
	 * @throws cs_AuthIsBlocked
	 * @throws cs_AuthIsExpired
	 * @throws cs_IncorrectSaltVersion
	 * @throws cs_ResendCodeCountLimitExceeded
	 * @throws cs_ResendWillBeAvailableLater
	 * @throws cs_UserAlreadyLoggedIn
	 * @throws cs_WrongAuthKey
	 */
	public static function resendFullAuthCode(int $user_id, string $auth_map):Struct_User_Auth_Info {

		// проверяем, что нет текущей активной сессии
		Domain_User_Entity_Validator::assertNotLoggedIn($user_id);

		// получаем запись аутентификации
		$story = Domain_User_Entity_AuthStory::getByMap($auth_map);

		// делаем общие для всех типов аутентификаций проверки
		$story->assertNotExpired()
			->assertNotFinishedYet()
			->assertType([Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_LOGIN_BY_MAIL, Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_REGISTER_BY_MAIL]);

		// выбрасываем исключение, если это действие недоступно
		Domain_User_Action_Auth_Mail::assertFullConfirmScenarioAllowed($story->getType());

		// делаем проверки свойственные аутентификации по почте
		self::_throwIfCodeErrorCountLimitExceeded($story);
		self::_throwIfResendCountLimitExceeded($story);
		$story->getAuthMailHandler()
			->assertAccessEnteringCodeStage()
			->assertResendIsAvailable();

		$story = Domain_User_Action_Auth_Mail::resendCode($story);

		return $story->getAuthInfo();
	}

	/**
	 * отменяем/отвязываем попытку аутентификации
	 *
	 * @throws cs_UserAlreadyLoggedIn
	 * @throws cs_WrongAuthKey
	 */
	public static function cancel(int $user_id, string $auth_map):void {

		// проверяем, что нет текущей активной сессии
		Domain_User_Entity_Validator::assertNotLoggedIn($user_id);

		// получаем запись аутентификации
		$story = Domain_User_Entity_AuthStory::getByMap($auth_map);

		// удаляем информацию об аутентификации из кэша
		$story->clearAuthCache();
	}

	/**
	 * проверяем, достигнут ли лимит ввода неверного кода
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws cs_AuthIsBlocked
	 */
	protected static function _throwIfCodeErrorCountLimitExceeded(Domain_User_Entity_AuthStory $story):void {

		try {
			$story->getAuthMailHandler()->assertCodeErrorCountLimitNotExceeded();
		} catch (Domain_User_Exception_AuthStory_ErrorCountLimitExceeded) {
			throw new cs_AuthIsBlocked($story->getExpiresAt());
		}
	}

	/**
	 * проверяем, достигнут ли лимит переотправки кода
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws Domain_User_Exception_AuthStory_ResendCountLimitExceeded
	 */
	protected static function _throwIfResendCountLimitExceeded(Domain_User_Entity_AuthStory $story):void {

		try {
			$story->getAuthMailHandler()->assertResendCountLimitNotExceeded();
		} catch (cs_ResendCodeCountLimitExceeded) {
			throw new Domain_User_Exception_AuthStory_ResendCountLimitExceeded($story->getExpiresAt());
		}
	}
}