<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\CountryNotFound;
use BaseFrame\Exception\Domain\InvalidPhoneNumber;
use BaseFrame\Exception\Domain\LocaleTextNotFound;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Сценарии для работы с веб-сайтом on-premise решений.
 */
class Domain_User_Scenario_OnPremiseWeb {

	/**
	 * Метод получения информации при входе на сайт.
	 */
	public static function start(int $user_id):array {

		// доступные способы аутентификации
		$available_auth_method_list = Domain_User_Entity_Auth_Config::getAvailableMethodList();

		// собираем словарь
		$dictionary = self::_prepareStartDictionary();

		// собираем ограничения
		$restrictions = self::_prepareRestrictions();

		// если пользователь не авторизован
		if ($user_id === 0) {
			return [false, null, false, $dictionary, $available_auth_method_list, $restrictions];
		}

		// получаем информацию о пользователе
		$user_info = Type_User_Main::get($user_id);

		return [true, $user_info, Domain_User_Entity_User::isEmptyProfile($user_info), $dictionary, $available_auth_method_list, $restrictions];
	}

	/**
	 * собираем словарь dictionary, который возвращаем в global/start
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _prepareStartDictionary():array {

		return [
			"auth_sso_start_button_text" => Domain_User_Entity_Auth_Config::getSsoStartButtonText(),
		];
	}

	/**
	 * Собираем ограничения
	 * @return array
	 */
	protected static function _prepareRestrictions():array {

		return [
			"profile" => (array) Type_Restrictions_Config::getProfileRestrictions(),
		];
	}

	/**
	 * Начинает процесс аутентификации на веб-сайте.
	 * Для создания пользователя необходима действующая ссылка-приглашение.
	 *
	 * @throws CaseException
	 * @throws Domain_Link_Exception_TemporaryUnavailable
	 * @throws InvalidPhoneNumber
	 * @throws LocaleTextNotFound
	 * @throws \blockException
	 * @throws \busException
	 * @throws \userAccessException
	 * @throws cs_ActionNotAvailable
	 * @throws cs_IncorrectJoinLink
	 * @throws cs_JoinLinkIsNotActive
	 * @throws cs_JoinLinkIsUsed
	 * @throws cs_JoinLinkNotFound
	 * @throws cs_AuthIsBlocked
	 * @throws cs_PlatformNotFound
	 * @throws cs_RecaptchaIsRequired
	 * @throws cs_UserAlreadyInCompany
	 * @throws cs_UserAlreadyLoggedIn
	 * @throws cs_UserNotFound
	 * @throws cs_WrongRecaptcha
	 *
	 * @long очень много проверок
	 */
	public static function beginAuthentication(int $user_id, string $phone_number, string|false $grecaptcha_response, string|false $join_link):array {

		// проверяем, что нет текущей активной сессии
		Domain_User_Entity_Validator::assertNotLoggedIn($user_id);

		// форматируем номер телефона
		$phone_number = (new \BaseFrame\System\PhoneNumber($phone_number))->number();

		// получаем user_id по номеру
		[$existing_user_id, $has_sso_account] = Domain_User_Action_Auth_PhoneNumber::resolveUser($phone_number);
		self::_redirectAuthToSsoIfNeeded($existing_user_id, $has_sso_account);

		// если не нашли пользователя, то нужно обязательно проверить актуальность ссылки-приглашения
		if ($existing_user_id === 0) {

			// если пользователь не зарегистрирован и если ссылка не передана, то сразу завершаем процесс
			$join_link === false && throw new Domain_User_Exception_AuthStory_RegistrationWithoutInvite();
		}

		// валидируем переданную ссылку
		// тут есть тонкий момент — если мы будем делать так, то, зная номер, можно понять,
		// какие ссылки приглашения пользователь уже принимал/может принять, но пока оставим так
		// это не выглядит как критическая уязвимость, для решения нужно просто поднять эту проверку выше,
		// чтобы для существующих юзеров не проверять (но тогда после ввода кода можно словить ошибку ссылки)
		if ($join_link !== false) {

			try {

				// пытаемся распарсить текст со ссылкой
				[, $parsed_link] = Domain_Link_Action_Parse::do($join_link);

				if (!is_string($parsed_link) || $parsed_link === "") {
					throw new ParamException("passed incorrect join link");
				}

				// получаем детальную информацию о ссылке
				$invite_link_rel_row = Domain_Company_Entity_JoinLink_Main::getByLink($parsed_link);
			} catch (Domain_Link_Exception_LinkNotFound|cs_IncorrectJoinLink|cs_JoinLinkNotFound) {
				throw new CaseException(1000, "passed bad invite");
			}

			$validation_result = $existing_user_id === 0
				? Domain_Link_Entity_Link::validateBeforeRegistration($invite_link_rel_row)
				: Domain_Link_Entity_Link::validateForUser($existing_user_id, $invite_link_rel_row);
		}

		try {

			// получаем значение из кеша, если есть, иначе дальше начнем регистрацию/логин
			$auth_story = Domain_User_Entity_AuthStory::getFromSessionCache($phone_number)
				->assertNotExpired()
				->assertAuthParameter($phone_number);
		} catch (cs_CacheIsEmpty|cs_AuthIsExpired|Domain_User_Exception_AuthStory_AuthParameterNotEqual|cs_CookieIsEmpty) {

			if ($existing_user_id === 0) {

				Domain_User_Entity_Antispam_Auth::checkBlocksBeforeStartRegisterByPhoneNumber($phone_number, $grecaptcha_response, true);
				$auth_story = Domain_User_Action_Auth_PhoneNumber::beginRegistration($phone_number);
			} else {

				Domain_User_Entity_Antispam_Auth::checkBlocksBeforeStartLoginByPhoneNumber($phone_number, $grecaptcha_response, true);
				$auth_story = Domain_User_Action_Auth_PhoneNumber::beginLogin($existing_user_id, $phone_number);
			}

			// сохраняем в кэш, отдаем данные для пользователя
			$auth_story->storeInSessionCache();
		}

		if (isset($validation_result)) {

			// сохраняем ссылку-приглашение если есть в кэш
			// это нам пригодится в дальнейшем, чтобы проверить переданные от клиента данные
			ShardingGateway::cache()->set(
				$auth_story->getAuthMap(),
				$validation_result->invite_link_rel->join_link_uniq
			);
		}

		return [
			$auth_story->getAuthInfo(),
			$validation_result ?? false,
		];
	}

	/**
	 * перенаправляем на способ аутентификации через SSO
	 *
	 * @throws Domain_User_Exception_AuthStory_RedirectToSso
	 */
	protected static function _redirectAuthToSsoIfNeeded(int $existing_user_id, bool $has_sso_account):void {

		// если не нашли пользователя, то ничего не делаем
		if ($existing_user_id < 1) {
			return;
		}

		// если к этому пользователю не привязан SSO аккаунт, то ничего не делаем
		if (!$has_sso_account) {
			return;
		}

		// если отключена аутентификация через SSO, то ничего не делаем
		if (!Domain_User_Entity_Auth_Method::isMethodAvailable(Domain_User_Entity_Auth_Method::METHOD_SSO)) {
			return;
		}

		throw new Domain_User_Exception_AuthStory_RedirectToSso();
	}

	/**
	 * Проверяет код подтверждения и генерирует токен аутентификации.
	 *
	 * @throws Domain_User_Exception_PhoneNumberBinding
	 * @throws InvalidPhoneNumber
	 * @throws \busException
	 * @throws \userAccessException
	 * @throws cs_AuthAlreadyFinished
	 * @throws cs_AuthIsExpired
	 * @throws cs_DamagedActionException
	 * @throws cs_InvalidConfirmCode
	 * @throws cs_InvalidHashStruct
	 * @throws cs_AuthIsBlocked
	 * @throws cs_UserAlreadyLoggedIn
	 * @throws cs_WrongAuthKey
	 * @throws cs_WrongCode
	 * @long try..catch
	 */
	public static function confirmAuthentication(int $user_id, string $auth_map, string $sms_code, string|false $join_link_uniq = false):array {

		// проверяем, что нет текущей активной сессии
		Domain_User_Entity_Validator::assertNotLoggedIn($user_id);
		Domain_User_Entity_Validator::assertValidConfirmCode($sms_code);

		// получаем запись аутентификации
		$story = Domain_User_Entity_AuthStory::getByMap($auth_map);

		try {

			// делаем общие для всех типов аутентификаций проверки
			$story->assertNotExpired()
				->assertNotFinishedYet();

			// делаем проверки свойственные аутентификации по номеру телефона
			$story->getAuthPhoneHandler()
				->assertErrorCountLimitNotExceeded(Domain_User_Entity_AuthStory_MethodHandler_PhoneNumber::ON_PREMISE_ERROR_COUNT_LIMIT)
				->assertEqualCode($sms_code, Domain_User_Entity_AuthStory_MethodHandler_PhoneNumber::ON_PREMISE_ERROR_COUNT_LIMIT);
		} catch (cs_WrongCode $e) {

			// записываем в историю ошибку подтверждения и обновляем кэш
			$story->getAuthPhoneHandler()->handleWrongCode();
			$story->storeInSessionCache();

			// если получен неверный код, и достигнут лимит, то кидаем ошибку лимита
			self::_throwIfErrorCountLimitExceeded($story);

			throw $e;
		} catch (Domain_User_Exception_AuthStory_ErrorCountLimitExceeded) {
			throw new cs_AuthIsBlocked($story->getExpiresAt());
		}

		/**
		 * @noinspection PhpUnusedLocalVariableInspection
		 * @var Struct_Integration_Notifier_Response_OnUserRegistered|null $integration_response
		 */
		[$user_id, $invite_accept_info, $integration_response] = $story->isNeedToCreateUser()
			? static::_confirmNotRegisteredUserAuthentication($story, $join_link_uniq)
			: static::_confirmRegisteredUserAuthentication($story->getUserId(), $story, $join_link_uniq);

		// чистим кэш попыток аутентификации для номера
		$story->clearAuthCache();

		// выдаем пользовательскую сессию
		Type_Session_Main::doLoginSession($user_id);

		// устанавливаем, что аутентификация прошла успешно
		$story->handleSuccess($user_id);
		Domain_User_Entity_Antispam_Auth::successAuth($story->getAuthPhoneHandler()->getPhoneNumber());
		self::_onSuccessAuth($story, $user_id);

		return [
			Domain_Solution_Action_GenerateAuthenticationToken::exec($user_id, join_link_uniq: $join_link_uniq),
			Type_User_Main::isEmptyProfile($user_id),
			!is_null($integration_response) ? $integration_response->action_list : [],
		];
	}

	/**
	 * после успешной аутентификации
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \queryException
	 */
	protected static function _onSuccessAuth(Domain_User_Entity_AuthStory $story, int $user_id):void {

		// добавляем аутентификацию в историю
		Gateway_Db_PivotHistoryLogs_UserAuthHistory::insert($story->getAuthMap(), $user_id, Domain_User_Entity_AuthStory::HISTORY_AUTH_STATUS_SUCCESS, time(), 0);

		// пишем статистику по введенной смс
		Gateway_Bus_CollectorAgent::init()->add("sms_history", [
			"uniq_key"     => $story->getAuthPhoneHandler()->getSmsID(),
			"is_success"   => 1,
			"resend_count" => $story->getAuthPhoneHandler()->getResendCount(),
			"error_count"  => $story->getAuthPhoneHandler()->getErrorCount(),
			"created_at"   => $story->getAuthPhoneHandler()->getCreatedAt(),
		]);
	}

	/**
	 * проверяем, достигнут ли лимит ввода неверного кода
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws cs_AuthIsBlocked
	 */
	protected static function _throwIfErrorCountLimitExceeded(Domain_User_Entity_AuthStory $story):void {

		try {
			$story->getAuthPhoneHandler()->assertErrorCountLimitNotExceeded(Domain_User_Entity_AuthStory_MethodHandler_PhoneNumber::ON_PREMISE_ERROR_COUNT_LIMIT);
		} catch (Domain_User_Exception_AuthStory_ErrorCountLimitExceeded) {
			throw new cs_AuthIsBlocked($story->getExpiresAt());
		}
	}

	/**
	 * Выполняет кусок логики подтверждения аутентификации для уже зарегистрированного пользователя.
	 */
	protected static function _confirmRegisteredUserAuthentication(int $user_id, Domain_User_Entity_AuthStory $story, string|false $join_link_uniq):array {

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
		Domain_User_Entity_UserActionComment::addUserLoginAction($user_id, $story->getType(), $story->getAuthPhoneHandler()->getPhoneNumber(), getDeviceId(), getUa());
		return [$user_id, $invite_accept_info ?? false, null];
	}

	/**
	 * Выполняет кусок логики для создания нового пользователя и подтверждения аутентификации.
	 * @long
	 */
	protected static function _confirmNotRegisteredUserAuthentication(Domain_User_Entity_AuthStory $story, string|false $join_link_uniq):array {

		try {

			// проверяем, может номер уже зарегистрирован
			$user_id = Domain_User_Entity_Phone::getUserIdByPhone($story->getAuthPhoneHandler()->getPhoneNumber());
			return static::_confirmRegisteredUserAuthentication($user_id, $story, $join_link_uniq);
		} catch (cs_PhoneNumberNotFound) {

			// это нормально, просто проверка, что телефон не занят
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
		$user                 = Domain_User_Action_Create_Human::do($story->getAuthPhoneHandler()->getPhoneNumber(), "", "", getUa(), getIp(), "", "", [], 0, 0);
		$integration_response = Domain_Integration_Entity_Notifier::onUserRegistered(new Struct_Integration_Notifier_Request_OnUserRegistered(
			user_id: $user->user_id,
			auth_method: Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_REGISTER_BY_PHONE_NUMBER,
			registered_by_phone_number: $story->getAuthPhoneHandler()->getPhoneNumber(),
			registered_by_mail: "",
			join_link_uniq: $validation_result->invite_link_rel->join_link_uniq,
		));
		Type_Phphooker_Main::sendUserAccountLog($user->user_id, Type_User_Analytics::REGISTERED);

		return [$user->user_id, [$join_link_rel_row, $user, $validation_result], $integration_response];
	}

	/**
	 * Повторно запрашивает смс0код для аутентификации.
	 *
	 * @throws InvalidPhoneNumber
	 * @throws CountryNotFound
	 * @throws LocaleTextNotFound
	 * @throws cs_AuthAlreadyFinished
	 * @throws cs_AuthIsExpired
	 * @throws cs_AuthIsBlocked
	 * @throws cs_PlatformNotFound
	 * @throws cs_RecaptchaIsRequired
	 * @throws cs_ResendCodeCountLimitExceeded
	 * @throws cs_ResendWillBeAvailableLater
	 * @throws cs_WrongRecaptcha
	 * @throws cs_WrongAuthKey
	 */
	public static function resendAuthenticationCode(int $user_id, string $auth_map, string|false $grecaptcha_response):Struct_User_Auth_Info {

		// получаем story по ключу и проверяем, что переотправка доступна
		$story = Domain_User_Entity_AuthStory::getByMap($auth_map);

		// делаем общие для всех типов аутентификаций проверки
		$story->assertNotExpired()
			->assertNotFinishedYet();

		// делаем проверки свойственные аутентификации по номеру телефона
		try {

			self::_throwIfResendCountLimitExceeded($story);
			$story->getAuthPhoneHandler()
				->assertErrorCountLimitNotExceeded(Domain_User_Entity_AuthStory_MethodHandler_PhoneNumber::ON_PREMISE_ERROR_COUNT_LIMIT)
				->assertResendIsAvailable();
		} catch (Domain_User_Exception_AuthStory_ErrorCountLimitExceeded) {
			throw new cs_AuthIsBlocked($story->getExpiresAt());
		}

		Domain_User_Entity_Antispam_Auth::checkBlocksBeforeStartResend($story->getAuthPhoneHandler()->getPhoneNumber(), $grecaptcha_response, true);

		$story = Domain_User_Action_Auth_PhoneNumber::resend($story);
		$story->storeInSessionCache();

		$phone_number_obj = new \BaseFrame\System\PhoneNumber($story->getAuthPhoneHandler()->getPhoneNumber());

		Type_Phphooker_Main::onSmsResent(
			$user_id,
			$phone_number_obj->obfuscate(),
			$story->getAuthPhoneHandler()->getAvailableResendCount(),
			"auth",
			\BaseFrame\Conf\Country::get($phone_number_obj->countryCode())->getLocalizedName(),
			$story->getAuthPhoneHandler()->getSmsID(),
		);

		return $story->getAuthInfo();
	}

	/**
	 * проверяем, достигнут ли лимит переотправки кода
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws Domain_User_Exception_AuthStory_ResendCountLimitExceeded
	 */
	protected static function _throwIfResendCountLimitExceeded(Domain_User_Entity_AuthStory $story):void {

		try {
			$story->getAuthPhoneHandler()->assertResendCountLimitNotExceeded();
		} catch (cs_ResendCodeCountLimitExceeded) {
			throw new Domain_User_Exception_AuthStory_ResendCountLimitExceeded($story->getExpiresAt());
		}
	}

	/**
	 * Завершаем активную сессию пользователя.
	 * @throws \cs_RowIsEmpty
	 */
	public static function logout(int $user_id):void {

		try {
			Domain_User_Entity_Validator::assertLoggedIn($user_id);
		} catch (cs_UserNotLoggedIn) {
			return;
		}

		// разлогиниваем сессию пользователя
		Type_Session_Main::doLogoutSession($user_id);
	}
}
