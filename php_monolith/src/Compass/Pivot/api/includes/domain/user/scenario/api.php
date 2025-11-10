<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\InvalidPhoneNumber;
use BaseFrame\Exception\Domain\LocaleTextNotFound;
use BaseFrame\Restrictions\Exception\ActionRestrictedException;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\EndpointAccessDeniedException;
use BaseFrame\Server\ServerProvider;
use JetBrains\PhpStorm\ArrayShape;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Exception\Gateway\BusFatalException;

/**
 * Сценарии пользователя для API
 *
 * Class Domain_User_Scenario_Api
 */
class Domain_User_Scenario_Api
{
	protected const _MAX_COLOR_SELECTION_LIST_COUNT = 500; // максимальное количество выделений

	/**
	 * Сценарий регистрации
	 *
	 *
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws InvalidPhoneNumber
	 * @throws LocaleTextNotFound
	 * @throws \blockException
	 * @throws \queryException
	 * @throws cs_ActionNotAvailable
	 * @throws cs_AuthIsBlocked
	 * @throws cs_IncorrectSaltVersion
	 * @throws cs_PlatformNotFound
	 * @throws cs_RecaptchaIsRequired
	 * @throws cs_UserAlreadyLoggedIn
	 * @throws cs_WrongRecaptcha
	 */
	public static function startAuth(int $user_id, string $phone_number, string | false $grecaptcha_response): Struct_User_Auth_Info
	{

		Domain_User_Entity_Validator::assertNotLoggedIn($user_id);
		Domain_User_Entity_Validator::assertBanned($user_id);
		$phone_number = (new \BaseFrame\System\PhoneNumber($phone_number))->number();
		Domain_Phone_Entity_Validator::assertBanned($phone_number);

		try {

			// получаем значение из кеша, если есть, иначе дальше начнем регистрацию/логин
			return Domain_User_Entity_AuthStory::getFromSessionCache($phone_number)
				->assertNotExpired()
				->assertAuthParameter($phone_number)
				->getAuthInfo();
		} catch (cs_CacheIsEmpty | cs_AuthIsExpired | Domain_User_Exception_AuthStory_AuthParameterNotEqual) {

			// пробуем залогинить, если такого номера нет, то регистрируем
			try {

				// получаем user_id по номеру
				$user_id = Domain_User_Entity_Phone::getUserIdByPhone($phone_number);
				Domain_User_Entity_Validator::assertBanned($user_id);
				Domain_User_Entity_Antispam_Auth::checkBlocksBeforeStartLoginByPhoneNumber($phone_number, $grecaptcha_response);
				$auth_story = Domain_User_Action_Auth_PhoneNumber::beginLogin($user_id, $phone_number);
			} catch (cs_PhoneNumberNotFound) {

				Domain_User_Entity_Antispam_Auth::checkBlocksBeforeStartRegisterByPhoneNumber($phone_number, $grecaptcha_response);
				$auth_story = Domain_User_Action_Auth_PhoneNumber::beginRegistration($phone_number);
			}

			// сохраняем в кэш, отдаем данные для пользователя
			$auth_story->storeInSessionCache();
			return $auth_story->getAuthInfo();
		}
	}

	/**
	 * Сценарий подтверждения смс
	 *
	 * @throws Domain_User_Exception_Mail_BelongAnotherUser
	 * @throws Domain_User_Exception_PhoneNumberBinding
	 * @throws InvalidPhoneNumber
	 * @throws \busException
	 * @throws \queryException
	 * @throws \userAccessException
	 * @throws cs_AnswerCommand
	 * @throws cs_AuthAlreadyFinished
	 * @throws cs_AuthIsBlocked
	 * @throws cs_AuthIsExpired
	 * @throws cs_DamagedActionException
	 * @throws cs_InvalidConfirmCode
	 * @throws cs_PlatformNotFound
	 * @throws cs_PlatformVersionNotFound
	 * @throws cs_UserAlreadyLoggedIn
	 * @throws cs_WrongAuthKey
	 * @throws cs_WrongCode
	 * @long
	 */
	public static function tryConfirmAuth(int $user_id, string $auth_map, string $code): int
	{

		Domain_User_Entity_Validator::assertNotLoggedIn($user_id);
		Domain_User_Entity_Validator::assertValidConfirmCode($code);
		Domain_User_Entity_Validator::assertBanned($user_id);

		// получаем story по ключу
		$story        = Domain_User_Entity_AuthStory::getByMap($auth_map);
		$phone_number = $story->getAuthPhoneHandler()->getPhoneNumber();
		Domain_User_Entity_Validator::assertBanned($story->getUserId());
		Domain_Phone_Entity_Validator::assertBanned($phone_number);

		try {

			// делаем общие для всех типов аутентификаций проверки
			$story->assertNotExpired()->assertNotFinishedYet();

			// делаем проверки свойственные аутентификации по номеру телефона
			$story->getAuthPhoneHandler()
				->assertErrorCountLimitNotExceeded(Domain_User_Entity_AuthStory_MethodHandler_PhoneNumber::SAAS_ERROR_COUNT_LIMIT)
				->assertEqualCode($code, Domain_User_Entity_AuthStory_MethodHandler_PhoneNumber::SAAS_ERROR_COUNT_LIMIT);
		} catch (cs_WrongCode $e) {

			// записываем в историю ошибку подтверждения и обновляем кэш
			$story->getAuthPhoneHandler()->handleWrongCode();
			$story->storeInSessionCache();

			throw $e;
		} catch (Domain_User_Exception_AuthStory_ErrorCountLimitExceeded) {
			throw new cs_AuthIsBlocked($story->getExpiresAt());
		}

		$user_id = $story->getUserId();

		// фиксируем в аналитике, что пользователь использовал код из смс
		Type_Sms_Analytics_Story::onConfirm(
			$user_id,
			Type_Sms_Analytics_Story::STORY_TYPE_AUTH,
			$auth_map,
			$story->getExpiresAt(),
			$story->getAuthPhoneHandler()->getSmsID(),
			$story->getAuthPhoneHandler()->getPhoneNumber()
		);

		// увеличиваем счетчик использованных кодов из смс
		Gateway_Bus_CollectorAgent::init()->inc("row55");

		// если требуется создать нового пользователя
		if ($story->isNeedToCreateUser()) {

			try {

				// проверяем, может номер уже зарегистрирован
				$user_id = Domain_User_Entity_Phone::getUserIdByPhone($story->getAuthPhoneHandler()->getPhoneNumber());
				Type_User_Notifications::updateUserIdForDevice($user_id, getDeviceId());
			} catch (cs_PhoneNumberNotFound) {

				$default_partner_id = 0;

				// регистрируем
				$user    = Domain_User_Action_Create_Human::do($story->getAuthPhoneHandler()->getPhoneNumber(), "", "", getUa(), getIp(), "", "", [], 0, $default_partner_id);
				$user_id = $user->user_id;

				Type_Phphooker_Main::sendUserAccountLog($user_id, Type_User_Analytics::REGISTERED);
			}
		} else {

			// добавляем в историю, что пользователь залогинился
			Domain_User_Entity_UserActionComment::addUserLoginAction($user_id, $story->getType(), $story->getAuthPhoneHandler()->getPhoneNumber(), getDeviceId(), getUa());

			// обновляем user_id для текущего девайса
			Type_User_Notifications::updateUserIdForDevice($user_id, getDeviceId());
		}

		// чистим кэш и выдаем сессию
		$story->clearAuthCache();
		$login_type = ServerProvider::isSaas() ? Domain_User_Entity_SessionExtra::SAAS_SMS_LOGIN_TYPE : Domain_User_Entity_SessionExtra::ONPREMISE_SMS_LOGIN_TYPE;
		Type_Session_Main::doLoginSession($user_id, $login_type);
		Type_User_ActionAnalytics::sessionStart($user_id);

		if (!$story->isNeedToCreateUser()) {

			$user_agent     = getUa();
			$device_name    = Type_Api_Platform::getDeviceName($user_agent);
			$app_version    = Type_Api_Platform::getVersion($user_agent);
			$server_version = ServerProvider::isSaas() ? SAAS_VERSION : ONPREMISE_VERSION;
			Domain_User_Action_Security_Device_OnSuccessLogin::do($user_id, $login_type, $device_name, $app_version, $server_version);
		}

		// устанавливаем, что аутентификация прошла успешно
		$story->handleSuccess($user_id);
		Domain_User_Entity_Antispam_Auth::successAuth($story->getAuthPhoneHandler()->getPhoneNumber());
		self::_onSuccessAuth($story, $user_id);

		// проверяем пустой ли профиль пользователя, отдаем ошибку
		self::_throwIfProfileIsEmpty($user_id);
		return $user_id;
	}

	/**
	 * после успешной аутентификации
	 *
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	protected static function _onSuccessAuth(Domain_User_Entity_AuthStory $story, int $user_id): void
	{

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
	 * Сценарий переотправки
	 *
	 * @throws InvalidPhoneNumber
	 * @throws LocaleTextNotFound
	 * @throws \BaseFrame\Exception\Domain\CountryNotFound
	 * @throws \queryException
	 * @throws cs_AuthAlreadyFinished
	 * @throws cs_AuthIsBlocked
	 * @throws cs_AuthIsExpired
	 * @throws cs_PlatformNotFound
	 * @throws cs_RecaptchaIsRequired
	 * @throws cs_ResendCodeCountLimitExceeded
	 * @throws cs_ResendWillBeAvailableLater
	 * @throws cs_UserAlreadyLoggedIn
	 * @throws cs_WrongRecaptcha
	 */
	public static function resendCode(int $user_id, string $auth_map, string | false $grecaptcha_response): Struct_User_Auth_Info
	{

		Domain_User_Entity_Validator::assertNotLoggedIn($user_id);

		// получаем story по ключу и проверяем, что переотправка доступна
		try {
			$story = Domain_User_Entity_AuthStory::getByMap($auth_map);
		} catch (cs_WrongAuthKey) {
			throw new ReturnFatalException("auth with provided auth_key does not exist");
		}

		// делаем общие для всех типов аутентификаций проверки
		$story->assertNotExpired()
			->assertNotFinishedYet();

		// делаем проверки свойственные аутентификации по номеру телефона
		try {

			$story->getAuthPhoneHandler()
				->assertErrorCountLimitNotExceeded(Domain_User_Entity_AuthStory_MethodHandler_PhoneNumber::SAAS_ERROR_COUNT_LIMIT)
				->assertResendCountLimitNotExceeded()
				->assertResendIsAvailable();
		} catch (Domain_User_Exception_AuthStory_ErrorCountLimitExceeded) {
			throw new cs_AuthIsBlocked($story->getExpiresAt());
		}

		Domain_User_Entity_Antispam_Auth::checkBlocksBeforeStartResend($story->getAuthPhoneHandler()->getPhoneNumber(), $grecaptcha_response);

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
	 * Сценарий метода doStart
	 *
	 * @throws \busException
	 * @throws cs_AnswerCommand
	 * @throws cs_CompanyIncorrectDeviceId
	 * @throws cs_PlatformNotFound
	 * @throws cs_UserNotFound
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \userAccessException|BusFatalException
	 * @long
	 */
	public static function doStart(int $user_id, string $session_uniq, string $app_version): array
	{

		$platform = Type_Api_Platform::getPlatform();
		$app_name = Type_Api_Platform::getAppNameByUserAgent();

		Domain_Company_Entity_Validator::assertDeviceId(getDeviceId());

		if ($user_id === 0) {

			// чистим токены для этого устройства
			Type_User_Notifications::clearUserCompanyPushTokenList(getDeviceId());
		}

		$ws_token                   = "";
		$ws_url                     = "";
		$announcement_initial_token = "";
		$notification_preferences   = [];
		$call_preferences           = [];
		$userbot_preferences        = [];
		$client_connection_token    = "";

		if ($user_id > 0) {

			// получаем информацию о пользователе
			$user_info = Gateway_Bus_PivotCache::getUserInfo($user_id);

			[$ws_token, $ws_url]        = self::_getWsTokenAndUrlAuthUser($user_id, getDeviceId(), $platform);
			$notification_preferences   = self::_getNotificationPreferencesAuthUser($user_id);
			$call_preferences           = self::_getCallPreferencesAuthUser($user_id);
			$userbot_preferences        = self::_getUserbotPreferences();
			$announcement_initial_token = self::_generateInitialTokenForAnnouncement($user_id);
			Gateway_Db_PivotSystem_OnlineUserByDayAll::insert($user_id);

			// проверяем, что пользователь впервые за день появился онлайн
			Domain_User_Action_CheckAppReturn::do($user_info);

			// обновляем цвет аватарки
			Domain_User_Action_UpdateAvatarColor::do($user_id);

			// генерируем лицензионный токен
			$client_connection_token = Domain_Solution_Entity_ClientConnectionToken::generate($user_id);

			// обновляем информацию активной сессии, если требуется
			Domain_User_Action_Security_Device_UpdateSessionIfNeed::do($user_id, $session_uniq, $app_version);
		}

		// получаем конфиг приложения для пользователя
		$app_config = Domain_User_Entity_Feature::getAppConfigForUser($platform, $app_name, $user_id, $app_version);

		// получаем время сервера и часовую зону
		$server_time = time();
		$time_zone   = intval(date("Z", time()));

		// добавляем ip в список на проверку
		Domain_Subnet_Action_AddIpToCheckList::do(getIp());

		return [
			$app_config,
			$server_time,
			$time_zone,
			$ws_token,
			$ws_url,
			BILLING_URL,
			$notification_preferences,
			$call_preferences,
			$announcement_initial_token,
			$userbot_preferences,
			$client_connection_token,
			Type_Captcha_Main::init()->getPublicCaptchaKey(Type_Api_Platform::getPlatform(getUa())),
			Type_Captcha_Main::getProviderPublicCaptchaData(Type_Api_Platform::getPlatform(getUa())),
		];
	}

	/**
	 * Получить токен пользователя для подключения к анонсам
	 */
	public static function getAnnouncementAuthorizationToken(int $user_id, string $device_id): string
	{

		if ($device_id === "") {
			throw new ParamException("incorrect device id");
		}

		// получаем все компании где состоит пользователь
		$all_active_company_list = Gateway_Db_PivotUser_CompanyList::getCompanyList($user_id);

		// получаем все компании где состоит пользователь из лобби на случай если компания была удалена
		$all_lobby_company_list = Gateway_Db_PivotUser_CompanyLobbyList::getCompanyListWithMinOrder($user_id, 0, 100);

		// фильтруем, что получить именно те компании, что удалены - мы не должны иметь доступ к компаниям, в которые нас еще не приняли
		$all_lobby_company_list = array_filter($all_lobby_company_list, function (Struct_Db_PivotUser_CompanyLobby $company_lobby) {

			return $company_lobby->status === Domain_Company_Entity_Company::COMPANY_STATUS_DELETED;
		});

		// мержим и формируем список id компаний
		$company_list    = array_merge($all_active_company_list, $all_lobby_company_list);
		$company_id_list = array_column($company_list, "company_id");
		$company_id_list = array_unique($company_id_list);

		$authorization_token = "";
		try {

			// формируем токен на основе массива id компаний
			$authorization_token = Gateway_Announcement_Main::getRegisterToken($user_id, $device_id, $company_id_list);
		} catch (\Exception) {
			// ничего не делаем, главное не падать
		}
		return $authorization_token;
	}

	/**
	 * Получить данные для соединения по WS
	 *
	 * @throws cs_PlatformNotFound
	 * @throws \parseException|BusFatalException
	 */
	public static function getConnection(int $user_id): array
	{

		$platform = Type_Api_Platform::getPlatform();

		return Gateway_Bus_SenderBalancer::getConnection($user_id, getDeviceId(), $platform);
	}

	/**
	 * Получаем список флагов
	 */
	public static function getFlagList(): array
	{

		return Domain_User_Entity_Flag::getCountryFlagList();
	}

	/**
	 * Сценарий обновления профиля
	 *
	 * @throws ActionRestrictedException
	 * @throws BusFatalException
	 * @throws Domain_User_Exception_AvatarIsDeleted
	 * @throws \busException
	 * @throws \cs_InvalidProfileName
	 * @throws \cs_RowIsEmpty
	 * @throws \queryException
	 * @throws cs_FileIsNotImage
	 * @throws cs_InvalidAvatarFileMap
	 */
	public static function setProfile(int $user_id, string | false $name, string | false $avatar_file_map, bool $is_operator = false): Struct_User_Info
	{

		if ($name !== false) {

			$name = Domain_User_Entity_Sanitizer::sanitizeProfileName($name);
			Domain_User_Entity_Validator::assertValidProfileName($name);
		}
		if ($avatar_file_map !== false) {

			Domain_User_Entity_Validator::assertValidAvatarFileMap($avatar_file_map);
			$avatar_file_key = Type_Pack_File::doEncrypt($avatar_file_map);

			// получаем аватар, чтобы убедиться, что он не удален
			$is_deleted = Gateway_Socket_PivotFileBalancer::checkIsDeleted($avatar_file_key);

			if ($is_deleted) {
				throw new Domain_User_Exception_AvatarIsDeleted("avatar is deleted");
			}
		}

		// если это on-premise пользователь пытается сменить имя, но включена аутентификация через SSO и пользователь зарегистрирован через SSO, то выбрасываем ошибку
		// об ограничении на это действие
		if (ServerProvider::isOnPremise() && $name !== false
			&& (Domain_User_Entity_Auth_Method::isMethodAvailable(Domain_User_Entity_Auth_Method::METHOD_SSO)
				|| Domain_User_Entity_Auth_Method::isGuestMethodAvailable(Domain_User_Entity_Auth_Method::METHOD_SSO))
			&& Gateway_Socket_Federation::hasSsoUserRelationship($user_id)) {
			throw new ActionRestrictedException("action is restricted");
		}

		[$is_profile_was_empty, $user_info] = Domain_User_Action_UpdateProfile::do($user_id, $name, $avatar_file_map);

		// отправляем задачу на обновление профиля в intercom
		if (!$is_operator) {

			Gateway_Socket_Intercom::userSetProfile($user_id, $name, $avatar_file_map);
		}

		// если это не оператор и пользователь впервые заполнил профиль
		if (!$is_operator && $is_profile_was_empty) {
			Type_Phphooker_Main::sendBitrixOnUserRegistered($user_id);
		}

		return $user_info;
	}

	/**
	 * разлогинить пользователя
	 *
	 *
	 * @throws ParseFatalException
	 * @throws cs_IncorrectSaltVersion
	 * @throws \returnException
	 */
	public static function doLogout(int $user_id): void
	{

		try {

			Domain_User_Entity_Validator::assertLoggedIn($user_id);

			// разлогиниваем сессию пользователя
			Type_Session_Main::doLogoutSession($user_id);

			// открепляем текущий девайс от пользователя
			Type_User_Notifications::deleteDeviceForUser($user_id, getDeviceId());
		} catch (cs_UserNotLoggedIn) {
			// просто ничего не делаем
		}
	}

	/**
	 * Сценарий для отправки смс для 2аф
	 *
	 * @throws LocaleTextNotFound
	 * @throws \blockException
	 * @throws \cs_UnpackHasFailed
	 * @throws \queryException
	 * @throws cs_PlatformNotFound
	 * @throws cs_RecaptchaIsRequired
	 * @throws cs_TwoFaInvalidUser
	 * @throws cs_TwoFaIsExpired
	 * @throws cs_TwoFaIsFinished
	 * @throws cs_TwoFaTypeIsInvalid
	 * @throws cs_UserPhoneSecurityNotFound
	 * @throws cs_WrongAuthKey
	 * @throws cs_WrongRecaptcha
	 */
	public static function trySendTwoFaSms(int $user_id, string $two_fa_map, string | false $grecaptcha_response): Domain_User_Entity_Confirmation_TwoFa_Story
	{

		try {

			$story = Domain_User_Entity_Confirmation_TwoFa_Story::getByMap($two_fa_map);
			$story->assertNotExpired()
				->assertNotFinished()
				->assertCorrectUser($user_id);
		} catch (cs_WrongTwoFaKey) {

			$two_fa_story = Domain_User_Entity_Confirmation_TwoFa_TwoFa::getByMap($two_fa_map);
			$two_fa_story->assertNotExpired()
				->assertNotFinished()
				->assertCorrectUser($user_id);
			$two_fa_data = $two_fa_story->getData();

			Domain_User_Entity_Antispam_TwoFa::checkBeforeSendTwoFaSms($user_id, $two_fa_data->company_id, $grecaptcha_response);

			$two_fa_phone_data = Domain_User_Action_TwoFa_SendSms::do($user_id, $two_fa_data);

			$story = new Domain_User_Entity_Confirmation_TwoFa_Story($two_fa_data, $two_fa_phone_data);
		}

		return $story;
	}

	/**
	 * сценарий для подтерждения 2fa смс
	 *
	 * @throws cs_ErrorCountLimitExceeded
	 * @throws cs_IncorrectSaltVersion
	 * @throws cs_InvalidHashStruct
	 * @throws cs_TwoFaInvalidUser
	 * @throws cs_TwoFaIsExpired
	 * @throws cs_TwoFaIsFinished
	 * @throws \cs_UnpackHasFailed
	 * @throws cs_WrongCode
	 * @throws cs_WrongTwoFaKey
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function tryConfirmTwoFaSms(int $user_id, string $two_fa_map, string $sms_code): void
	{

		$story = Domain_User_Entity_Confirmation_TwoFa_Story::getByMap($two_fa_map);

		try {

			$story->assertCorrectUser($user_id)
				->assertNotExpired()
				->assertNotFinished()
				->assertErrorCountLimitNotExceeded()
				->assertEqualCode($sms_code)
				->assertPhoneConfirmed();
		} catch (cs_WrongCode $e) {

			Domain_User_Action_TwoFa_HandleWrongCode::do($story);

			throw $e;
		} catch (cs_PhoneIsNotConfirmed) {

			Domain_User_Entity_Antispam_TwoFa::successTwoFaConfirm($user_id, $story->getTwoFaInfo()->company_id);
			Domain_User_Action_TwoFa_Confirm::do($story);
		}

		// фиксируем в аналитике, что пользователь использовал код из смс
		Type_Sms_Analytics_Story::onConfirm(
			$user_id,
			Type_Sms_Analytics_Story::STORY_TYPE_TWO_FA,
			$two_fa_map,
			$story->getTwoFaInfo()->expires_at,
			$story->getPhoneInfo()->sms_id,
			$story->getPhoneInfo()->phone_number
		);

		// увеличиваем счетчик использованных кодов из смс
		Gateway_Bus_CollectorAgent::init()->inc("row55");
	}

	/**
	 * Сценарий для переотправки смс для 2fa
	 *
	 * @throws cs_ErrorCountLimitExceeded
	 * @throws cs_IncorrectSaltVersion
	 * @throws cs_PlatformNotFound
	 * @throws cs_RecaptchaIsRequired
	 * @throws cs_ResendCodeCountLimitExceeded
	 * @throws cs_ResendWillBeAvailableLater
	 * @throws cs_TwoFaInvalidUser
	 * @throws cs_TwoFaIsExpired
	 * @throws cs_TwoFaIsFinished
	 * @throws \cs_UnpackHasFailed
	 * @throws cs_WrongRecaptcha
	 * @throws cs_WrongTwoFaKey
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function tryResendTwoFaSms(int $user_id, string $two_fa_map, string | false $grecaptcha_response): Domain_User_Entity_Confirmation_TwoFa_Story
	{

		$story = Domain_User_Entity_Confirmation_TwoFa_Story::getByMap($two_fa_map);

		$story->assertNotExpired()
			->assertNotFinished()
			->assertErrorCountLimitNotExceeded()
			->assertResendCountLimitNotExceeded()
			->assertResendIsAvailable()
			->assertCorrectUser($user_id);

		$two_fa_info = $story->getTwoFaInfo();
		Domain_User_Entity_Antispam_TwoFa::checkBeforeSendTwoFaSms($user_id, $two_fa_info->company_id, $grecaptcha_response);

		$story = Domain_User_Action_TwoFa_ResendSms::do($story);

		$phone_number_obj = new \BaseFrame\System\PhoneNumber($story->getPhoneInfo()->phone_number);

		Type_Phphooker_Main::onSmsResent(
			$user_id,
			$phone_number_obj->obfuscate(),
			Domain_User_Entity_Confirmation_TwoFa_Story::RESEND_COUNT_LIMIT - $story->getPhoneInfo()->resend_count,
			"2fa",
			\BaseFrame\Conf\Country::get($phone_number_obj->countryCode())->getLocalizedName(),
			$story->getPhoneInfo()->sms_id
		);

		return $story;
	}

	/**
	 * Сценарий для очистки аватара пользователя
	 *
	 * @throws \parseException|ActionRestrictedException
	 */
	public static function doClearAvatar(int $user_id): void
	{

		if (!Type_Restrictions_Config::isAvatarChangeEnabled()) {
			throw new ActionRestrictedException("action is restricted");
		}

		Domain_User_Action_ClearAvatar::do($user_id);

		// отправляем задачу на очистку аватара в intercom
		Gateway_Socket_Intercom::userDoClearAvatar($user_id);
	}

	/**
	 * Получаем инфу о типе личности
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getMBTIInfo(int $user_id, string $mbti_type): array
	{

		if (!Domain_User_Entity_Validator::isMBTIType($mbti_type)) {
			throw new ParamException(__METHOD__ . ": select is not available mbti_type");
		}

		$mbti_selection_list = Gateway_Db_PivotUser_MbtiSelectionList::getAllByUserIdAndMbtiType($user_id, $mbti_type);

		// получаем конфиг и меняем тэг <b> на звездочку
		$config                                  = getConfig("MBTI_INFO");
		$config[$mbti_type]["short_description"] = str_replace(["<b>", "</b>"], "*", $config[$mbti_type]["short_description"]);
		$config[$mbti_type]["description"]       = str_replace(["<b>", "</b>"], "*", $config[$mbti_type]["description"]);

		$short_description_color_selection_list = [];
		$description_color_selection_list       = [];

		foreach ($mbti_selection_list as $v) {

			$color_selection_list = self::_formatColorSelectionList($v["color_selection_list"]);
			if ($v["text_type"] === "short_description") {
				$short_description_color_selection_list = $color_selection_list;
			}
			if ($v["text_type"] === "description") {
				$description_color_selection_list = $color_selection_list;
			}
		}

		return self::_outputGetMBTIInfo($mbti_type, $config, $short_description_color_selection_list, $description_color_selection_list);
	}

	/**
	 * возвращаем ответ для getMBTIInfo
	 *
	 * @throws \cs_RowIsEmpty
	 */
	#[ArrayShape([
		"file_key"          => "string",
		"short_description" => "object",
		"description"       => "object",
	])]
	protected static function _outputGetMBTIInfo(string $mbti_type, array $config, array $short_description_color_selection_list, array $description_color_selection_list): array
	{

		$mbti_file = Gateway_Db_PivotSystem_DefaultFileList::get("mbti_document_" . mb_strtolower($mbti_type));

		return [
			"file_key"          => (string) $mbti_file->file_key,
			"short_description" => (object) [
				"text"                 => (string) $config[$mbti_type]["short_description"],
				"color_selection_list" => (array) $short_description_color_selection_list,
			],
			"description"       => (object) [
				"text"                 => (string) $config[$mbti_type]["description"],
				"color_selection_list" => (array) $description_color_selection_list,
			],
		];
	}

	/**
	 * Устанавливаем выделения цветом
	 *
	 * @throws cs_ExceededColorSelectionList
	 */
	public static function setColorSelectionList(int $user_id, string $mbti_type, string $text_type, array $color_selection_list): void
	{

		// проверяем color_selection_list
		if (!Domain_User_Entity_Validator::isMbtiColorSelectionList($color_selection_list)) {
			throw new ParamException(__METHOD__ . ": select is not available color_selection_list");
		}

		if (count($color_selection_list) > self::_MAX_COLOR_SELECTION_LIST_COUNT) {
			throw new cs_ExceededColorSelectionList();
		}

		// форматируем color_selection_list
		$color_selection_list = self::_formatColorSelectionList($color_selection_list);

		// проводим валидацию mbti_type
		if (!Domain_User_Entity_Validator::isMBTIType($mbti_type)) {
			throw new ParamException(__METHOD__ . ": select is not available mbti_type");
		}

		// проводим валидацию text_type
		if (!Domain_User_Entity_Validator::isMBTITextType($text_type)) {
			throw new ParamException(__METHOD__ . ": select is not available text_type");
		}

		// добавляем новый цвет
		$insert = new Struct_Db_PivotUser_MbtiSelectionList(
			$user_id,
			$mbti_type,
			$text_type,
			$color_selection_list
		);
		Gateway_Db_PivotUser_MbtiSelectionList::insertOrUpdate($insert);
	}

	/**
	 * Начать смену номера телефона
	 *
	 * @throws ActionRestrictedException
	 * @throws BlockException
	 * @throws Domain_User_Exception_AuthMethodDisabled
	 * @throws ParseFatalException
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws \queryException
	 * @throws cs_IncorrectSaltVersion
	 * @throws cs_PhoneChangeSmsErrorCountExceeded
	 * @throws cs_PhoneChangeSmsResendCountExceeded
	 * @throws cs_UserPhoneSecurityNotFound
	 * @throws cs_blockException
	 * @throws Domain_User_Exception_Security_UserWasRegisteredBySso
	 * @long
	 */
	public static function changePhoneStep1(int $user_id, string $session_uniq): array
	{

		if (!Type_Restrictions_Config::isPhoneChangeEnabled()) {
			throw new ActionRestrictedException("action is restricted");
		}

		// проверяем что добавление номера телефона не отключено в конфиге
		Domain_User_Entity_Auth_Method::assertMethodEnabled(Domain_User_Entity_Auth_Method::METHOD_PHONE_NUMBER);

		// проверяем что у пользователя есть номер телефона
		Domain_User_Entity_Phone::getPhoneByUserId($user_id);

		// проверяем что пользователь не зарегистрирован через SSO
		Domain_User_Entity_Phone::assertUserWasNotRegisteredBySso($user_id);

		// проверяем кэш, если пусто, или успешно завершена, или запись устарела, создаем новую
		try {

			// получаем из кэша запись о смене номера и проверяем, активна ли она
			$story = Domain_User_Entity_ChangePhone_Story::getFromSessionCache()
				->assertNotExpire()
				->assertNotSuccess()
				->assertFirstStage();

			try {

				// получаем запись об смс для этой смены номера и проверяем, актуальна ли
				$sms_story = $story->getActiveFirstSmsStoryEntity()
					->assertErrorCountNotExceeded()
					->assertResendCountNotExceeded();
			} catch (cs_PhoneChangeSmsErrorCountExceeded $e) {

				// это исключение выбрасывается в функции assertErrorCountNotExceeded() и нигде выше
				// так что делаем try catch внутри try catch ...
				// ... «мы сделали try catch внутри другого try catch ...»

				// выкидываем ошибку о том, что смена номера временно заблокирована (из-за превышения кол-ва ошибок)
				$e->setNextAttempt($story->getExpiresAt());
				throw $e;
			}
		} catch (cs_CacheIsEmpty | cs_PhoneChangeIsExpired | cs_PhoneChangeIsSuccess | cs_PhoneChangeSmsNotFound | cs_PhoneChangeWrongStage) {

			// проверяем блокировку
			Type_Antispam_User::throwIfBlocked($user_id, Type_Antispam_User::PHONE_CHANGE);

			// выполняем начало смены номера телефона и сохраняем в кэше
			/** @var Domain_User_Entity_ChangePhone_Story $story */
			[$story, $sms_story] = Domain_User_Action_ChangePhone_FirstStage::do($user_id, $session_uniq);
			$story->storeInSessionCache();

			// увеличиваем счетчик отправленных смс
			Gateway_Bus_CollectorAgent::init()->inc("row54");

			// добавляем в phphooker задачу, чтобы в случае протухания попытки – скинуть лог в аналитику
			Type_Phphooker_Main::onPhoneChangeStoryExpire($user_id, $story->getStoryMap(), $story->getExpiresAt());
		}

		return [$story, $sms_story];
	}

	/**
	 * Подтвердить смс при смене номера
	 *
	 * @throws BusFatalException
	 * @throws Domain_User_Exception_AuthMethodDisabled
	 * @throws Domain_User_Exception_PhoneNumberBinding
	 * @throws InvalidPhoneNumber
	 * @throws \busException
	 * @throws \cs_UnpackHasFailed
	 * @throws \queryException
	 * @throws \userAccessException
	 * @throws cs_InvalidHashStruct
	 * @throws cs_PhoneChangeIsExpired
	 * @throws cs_PhoneChangeIsSuccess
	 * @throws cs_PhoneChangeSmsErrorCountExceeded
	 * @throws cs_PhoneChangeSmsNotFound
	 * @throws cs_PhoneChangeStoryWrongMap
	 * @throws cs_UserPhoneSecurityNotFound
	 * @throws cs_WrongCode
	 */
	public static function confirmSmsForChangePhone(int $user_id, string $change_phone_story_map, string $sms_code): string
	{

		// проверяем что добавление номера телефона не отключено в конфиге
		Domain_User_Entity_Auth_Method::assertMethodEnabled(Domain_User_Entity_Auth_Method::METHOD_PHONE_NUMBER);

		$story = Domain_User_Entity_ChangePhone_Story::getByMap($user_id, $change_phone_story_map)
			->assertUserAuthorized($user_id)
			->assertNotExpire()
			->assertNotSuccess();

		try {

			$sms_story = $story->getActiveSmsStoryForCurrentStage()
				->assertErrorCountNotExceeded();

			// проверяем код смс
			$sms_story->assertEqualSmsCode($sms_code);

			// получаем текущий этап
			$current_tage = $story->getStage();

			/** @var Domain_User_Entity_ChangePhone_Story $story */
			[$story, $updated_sms_story] = $story->doConfirmActionForCurrentStage($user_id, $sms_story);

			$story->storeInSessionCache();
			if ($current_tage === Domain_User_Entity_ChangePhone_Story::STAGE_SECOND) {
				Type_Session_Main::doLogoutUserSessionsExceptCurrent($user_id);
			}

			// фиксируем в аналитике, что пользователь использовал код из смс
			Type_Sms_Analytics_Story::onConfirm(
				$user_id,
				Type_Sms_Analytics_Story::STORY_TYPE_PHONE_CHANGE,
				$change_phone_story_map,
				$story->getExpiresAt(),
				$sms_story->getSmsId(),
				$sms_story->getPhoneNumber()
			);

			// увеличиваем счетчик использованных кодов из смс
			Gateway_Bus_CollectorAgent::init()->inc("row55");
		} catch (cs_PhoneChangeSmsErrorCountExceeded $e) {

			// выкидываем ошибку о том, что смена номера временно заблокирована (из-за превышения кол-ва ошибок)
			$e->setNextAttempt($story->getExpiresAt());
			throw $e;
		} catch (cs_WrongCode) {

			// увеличиваем счетчик, и если не осталось попыток, выкидываем блокировку
			$available_attempts = Domain_User_Action_ChangePhone_IncrementError::do($sms_story, $story);
			if ($available_attempts === 0) {
				throw new cs_PhoneChangeSmsErrorCountExceeded($story->getExpiresAt());
			}
			throw new cs_WrongCode($available_attempts, $story->getExpiresAt());
		}

		return $updated_sms_story->getSmsStoryData()->phone_number;
	}

	/**
	 * Выполняем ввод нового номера телефона при смене
	 *
	 *
	 * @throws InvalidPhoneNumber
	 * @throws BlockException
	 * @throws cs_IncorrectSaltVersion
	 * @throws cs_PhoneAlreadyAssignedToCurrentUser
	 * @throws cs_PhoneAlreadyRegistered
	 * @throws cs_PhoneChangeIsExpired
	 * @throws cs_PhoneChangeIsSuccess
	 * @throws cs_PhoneChangeSmsErrorCountExceeded
	 * @throws cs_PhoneChangeStoryWrongMap
	 * @throws cs_PhoneChangeWrongStage
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \userAccessException|Domain_User_Exception_AuthMethodDisabled
	 */
	public static function changePhoneStep2(int $user_id, string $change_phone_story_map, string $phone_number): array
	{

		// проверяем что добавление номера телефона не отключено в конфиге
		Domain_User_Entity_Auth_Method::assertMethodEnabled(Domain_User_Entity_Auth_Method::METHOD_PHONE_NUMBER);

		// проверяем новый номер телефона
		$phone_number = (new \BaseFrame\System\PhoneNumber($phone_number))->number();
		Domain_User_Entity_Validator::assertPhoneIsNotUsedUserOrAnother($user_id, $phone_number);

		$story = Domain_User_Entity_ChangePhone_Story::getByMap($user_id, $change_phone_story_map)
			->assertUserAuthorized($user_id)
			->assertNotExpire()
			->assertNotSuccess()
			->assertSecondStage();

		$old_sms_story = [];
		try {

			// пробуем получить последнюю запись
			$old_sms_story = $story->getActiveSecondSmsStoryEntity()->assertErrorCountNotExceeded();

			// проверяем, повторно ли ввели этот номер
			$old_sms_story->assertNotEqualPhoneNumber($phone_number);

			// проверяем блокировку (чтобы предотвратить слив баланса за счет постоянной смены номера в этом месте
			Type_Antispam_User::throwIfBlocked($user_id, Type_Antispam_User::PHONE_CHANGE);

			// блокируем прошлое смс и отправляем новое на новый номер
			return Domain_User_Action_ChangePhone_SecondStage::do($story, $phone_number, $old_sms_story);
		} catch (cs_PhoneChangeSmsNotFound) {

			// ни разу не отправляли еще на новый номер, поэтому просто отправляем
			return Domain_User_Action_ChangePhone_SecondStage::do($story, $phone_number);
		} catch (cs_PhoneChangeSmsStoryAlreadyExist) {

			// если номер ввели второй раз подряд, то отправляем уже существующие данные (повторно отправлять смс не требуется)
			return [$story, $old_sms_story];
		} catch (cs_PhoneChangeSmsErrorCountExceeded $e) {

			$e->setNextAttempt($story->getExpiresAt());
			throw $e;
		}
	}

	/**
	 * Переотправка смс при смене номера
	 *
	 * @throws Domain_User_Exception_AuthMethodDisabled
	 * @throws InvalidPhoneNumber
	 * @throws LocaleTextNotFound
	 * @throws \BaseFrame\Exception\Domain\CountryNotFound
	 * @throws \cs_UnpackHasFailed
	 * @throws \queryException
	 * @throws \userAccessException
	 * @throws cs_PhoneChangeIsExpired
	 * @throws cs_PhoneChangeIsSuccess
	 * @throws cs_PhoneChangeSmsErrorCountExceeded
	 * @throws cs_PhoneChangeSmsNotFound
	 * @throws cs_PhoneChangeSmsResendCountExceeded
	 * @throws cs_PhoneChangeSmsResendNotAvailable
	 * @throws cs_PhoneChangeStoryWrongMap
	 */
	public static function resendSmsForNumberChange(int $user_id, string $change_phone_story_map): array
	{

		// проверяем что добавление номера телефона не отключено в конфиге
		Domain_User_Entity_Auth_Method::assertMethodEnabled(Domain_User_Entity_Auth_Method::METHOD_PHONE_NUMBER);

		$story = Domain_User_Entity_ChangePhone_Story::getByMap($user_id, $change_phone_story_map)
			->assertUserAuthorized($user_id)
			->assertNotExpire()
			->assertNotSuccess();

		try {

			$sms_story = $story->getActiveSmsStoryForCurrentStage()
				->assertErrorCountNotExceeded()
				->assertResendCountNotExceeded()
				->assertResendIsAvailable();
		} catch (cs_PhoneChangeSmsErrorCountExceeded $e) {

			// выкидываем ошибку о том, что смена номера временно заблокирована (из-за превышения кол-ва ошибок)
			$e->setNextAttempt($story->getExpiresAt());
			throw $e;
		}

		[$story, $updated_sms_story] = Domain_User_Action_ChangePhone_ResendSms::do($sms_story, $story);

		$phone_number_obj = new \BaseFrame\System\PhoneNumber($updated_sms_story->getSmsStoryData()->phone_number);

		Type_Phphooker_Main::onSmsResent(
			$user_id,
			$phone_number_obj->obfuscate(),
			Domain_User_Entity_ChangePhone_SmsStory::MAX_RESEND_COUNT - $updated_sms_story->getSmsStoryData()->resend_count,
			"change_phone",
			\BaseFrame\Conf\Country::get($phone_number_obj->countryCode())->getLocalizedName(),
			$sms_story->getSmsStoryData()->sms_id
		);

		return [$story, $updated_sms_story];
	}

	/**
	 * Получить данные о номере телефона
	 *
	 *
	 * @throws InvalidPhoneNumber
	 * @throws cs_UserPhoneSecurityNotFound
	 */
	public static function getPhoneNumberInfo(int $user_id): \BaseFrame\System\PhoneNumber
	{

		$phone_number = Domain_User_Entity_Phone::getPhoneByUserId($user_id);

		return new \BaseFrame\System\PhoneNumber($phone_number);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Генерирует токен для получения глобальных анонсов
	 */
	protected static function _generateInitialTokenForAnnouncement(int $user_id): string
	{

		$payload = [
			"id" => $user_id,
		];

		return \Jwt::generate(SALT_INITIAL_ANNOUNCEMENT_TOKEN, $payload);
	}

	/**
	 * Форматируем список выделений цветом
	 */
	protected static function _formatColorSelectionList(array $color_selection_list): array
	{

		$output = [];
		foreach ($color_selection_list as $v) {

			$output[] = [
				"selection_id" => (int) $v["selection_id"],
				"position"     => (int) $v["position"],
				"length"       => (int) $v["length"],
				"color_id"     => (int) $v["color_id"],
			];
		}

		return $output;
	}

	/**
	 * Получаем информацию для звонков
	 */
	public static function getCallPreferences(int $user_id): array
	{

		// добавляем к ответу константу с максимальным количеством участников
		$constants = [
			[
				"name"  => "max_member_limit",
				"value" => CALL_MAX_MEMBER_LIMIT,
			],
		];

		// получаем последний звонок из кластера; проверяем, что запись найдена
		try {
			$last_call_row = Gateway_Db_PivotUser_UserLastCall::getOne($user_id);
		} catch (\cs_RowIsEmpty) {
			return [$constants, false];
		}

		// если имеется активный звонок, то добавляем его к ответу
		if ($last_call_row->is_finished != 0) {
			return [$constants, false];
		}

		$active_call = [
			"company_id" => $last_call_row->company_id,
			"call_key"   => $last_call_row->call_key,
		];

		return [$constants, $active_call];
	}

	/**
	 * удаляем аккаунт пользователя
	 *
	 * @throws BlockException
	 * @throws BusFatalException
	 * @throws Domain_User_Exception_Confirmation_Mail_InvalidMailPasswordStoryKey
	 * @throws Domain_User_Exception_Confirmation_Mail_InvalidUser
	 * @throws Domain_User_Exception_Confirmation_Mail_IsExpired
	 * @throws Domain_User_Exception_Confirmation_Mail_IsInvalidType
	 * @throws Domain_User_Exception_Confirmation_Mail_IsNotConfirmed
	 * @throws Domain_User_Exception_Confirmation_Mail_NotSuccess
	 * @throws Domain_User_Exception_Mail_NotFound
	 * @throws Domain_User_Exception_Mail_NotFoundOnSso
	 * @throws EndpointAccessDeniedException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \busException
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws cs_AnswerCommand
	 * @throws cs_TwoFaInvalidCompany
	 * @throws cs_TwoFaInvalidUser
	 * @throws cs_TwoFaIsExpired
	 * @throws cs_TwoFaIsFinished
	 * @throws cs_TwoFaIsNotActive
	 * @throws cs_TwoFaTypeIsInvalid
	 * @throws cs_UnknownKeyType
	 * @throws cs_UserAlreadyBlocked
	 * @throws cs_UserNotFound
	 * @throws cs_UserPhoneSecurityNotFound
	 * @throws cs_WrongTwoFaKey
	 * @throws cs_blockException
	 */
	public static function deleteProfile(int $user_id, string $session_uniq, string | false $two_fa_key, string | false $confirm_mail_password_story_key): void
	{

		$user_security = Gateway_Db_PivotUser_UserSecurity::getOne($user_id);

		if (ServerProvider::isOnPremise()) {

			// проверяем опцию возможности удаления аккаунта
			if (!Type_Restrictions_Config::isProfileDeletionEnabled()) {
				throw new Domain_User_Exception_ProfileDeletionDisabled("profile deletion disabled");
			}

			// если, пользователь зарегистрирован через ссо и отключен метод авторизации через ссо
			self::_checkIfUserWasRegisteredBySso($user_id, $user_security, $two_fa_key, $confirm_mail_password_story_key);

			// если аутентификация была через SSO
			if (!Domain_User_Entity_Mail::hasMail($user_security) && !Domain_User_Entity_Phone::hasPhoneNumber($user_security)) {
				throw new Domain_User_Exception_Mail_NotFoundOnSso("sso user");
			}

			if (Domain_User_Entity_Auth_Method::isSingleAuthMethodEnabled(Domain_User_Entity_Auth_Method::METHOD_SSO)
				&& (
					!Domain_User_Entity_Auth_Method::isGuestMethodAvailable(Domain_User_Entity_Auth_Method::METHOD_PHONE_NUMBER)
					&& !Domain_User_Entity_Auth_Method::isGuestMethodAvailable(Domain_User_Entity_Auth_Method::METHOD_MAIL)
				)) {

				throw new Domain_User_Exception_Mail_NotFoundOnSso("sso user");
			}

			// если аутентификация через номер телефона, то проверяем что есть номер телефона
			if (Domain_User_Entity_Auth_Method::isSingleAuthMethodEnabled(Domain_User_Entity_Auth_Method::METHOD_PHONE_NUMBER)) {

				try {
					Domain_User_Entity_Phone::getPhoneByUserId($user_id);
				} catch (cs_UserPhoneSecurityNotFound $e) {

					// бросаем только если нет гостевой авторизации по почте
					if (!Domain_User_Entity_Auth_Method::isGuestMethodAvailable(Domain_User_Entity_Auth_Method::METHOD_MAIL)) {
						throw $e;
					}
				}
			}

			// если аутентификация через почту, то проверяем что у пользователя установлена почта
			if (Domain_User_Entity_Auth_Method::isSingleAuthMethodEnabled(Domain_User_Entity_Auth_Method::METHOD_MAIL)) {

				try {
					Domain_User_Entity_Mail::assertAlreadyExistMail($user_security);
				} catch (Domain_User_Exception_Mail_NotFound $e) {

					// бросаем только если нет гостевой авторизации по номеру телефона
					if (!Domain_User_Entity_Auth_Method::isGuestMethodAvailable(Domain_User_Entity_Auth_Method::METHOD_PHONE_NUMBER)) {
						throw $e;
					}
				}
			}
		}

		// проверяем 2fa
		Domain_User_Entity_Confirmation_Main::handle(
			$user_security,
			$session_uniq,
			Domain_User_Entity_Confirmation_Main::CONFIRMATION_DELETE_PROFILE,
			$two_fa_key,
			$confirm_mail_password_story_key
		);

		// проверяем, может профиль уже заблочен и у пользователя не привязан номер телефона
		$user_info = Gateway_Bus_PivotCache::getUserInfo($user_id);
		if (Type_User_Main::isDisabledProfile($user_info->extra) && isEmptyString($user_security->phone_number)) {
			throw new cs_UserAlreadyBlocked("user is already blocked");
		}

		// проверяем, что не являемся рут пользователем
		if (ServerProvider::isOnPremise()) {
			Domain_User_Entity_OnpremiseRoot::assertNotRootUserId($user_id);
		}

		// удаляем аккаунт пользователя
		if (($two_fa_key !== false) || ($confirm_mail_password_story_key !== false)) {

			Domain_User_Action_DeleteProfile::do($user_id, $user_security);

			// отправляем событие об удалении аккаунта
			Gateway_Bus_SenderBalancer::profileDeleted($user_id, [$user_id]);

			// отправляем задачу на удаление профиля в intercom
			Gateway_Socket_Intercom::userProfileDeleted($user_id);

			// отправляем в партнерку
			Type_User_ActionAnalytics::send($user_id, Type_User_ActionAnalytics::DELETE_ACCOUNT);
		}

		if (ServerProvider::isOnPremise()) {

			// отправляем событие об удалении профиля в premise-модуль
			Domain_Premise_Entity_Event_UserProfileDeleted::create($user_id);

			// удаляем связь с SSO, если она имеется
			Gateway_Socket_Federation::deleteSsoUserRelationship($user_id);
		}
	}

	/**
	 * Выполняем проверки если пользователь был зарегистрирован через SSO а авторизацию через SSo отключили
	 *
	 * @throws Domain_User_Exception_Mail_NotFound
	 * @throws Domain_User_Exception_Mail_NotFoundOnSso
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_UserPhoneSecurityNotFound
	 */
	protected static function _checkIfUserWasRegisteredBySso(int $user_id, Struct_Db_PivotUser_UserSecurity $user_security, string | false $two_fa_key, string | false $confirm_mail_password_story_key): void
	{

		// если, пользователь зарегистрирован через ссо, иначе - выходим
		if (!Gateway_Socket_Federation::hasSsoUserRelationship($user_id)) {
			return;
		}

		// если отключены альтернативные методы авторизации
		if (!Domain_User_Entity_Auth_Config::isAuthorizationAlternativeEnabled()) {
			throw new Domain_User_Exception_Mail_NotFoundOnSso("sso user");
		}

		// если не привязана и почта и номер телефона
		if (!Domain_User_Entity_Mail::hasMail($user_security) && !Domain_User_Entity_Phone::hasPhoneNumber($user_security)) {
			throw new Domain_User_Exception_Mail_NotFound("mail not exist in user");
		}

		// если аутентификация через номер телефона, и удаляемся через телефон, то проверяем что есть номер телефона
		if (($two_fa_key !== false) && (Domain_User_Entity_Auth_Method::isMethodAvailable(Domain_User_Entity_Auth_Method::METHOD_PHONE_NUMBER)
				|| Domain_User_Entity_Auth_Method::isGuestMethodAvailable(Domain_User_Entity_Auth_Method::METHOD_PHONE_NUMBER))) {

			Domain_User_Entity_Phone::getPhoneByUserId($user_id);
		}

		// если аутентификация через почту, и удаляемся через почту, то проверяем что у пользователя установлена почта
		if (($confirm_mail_password_story_key !== false) && (Domain_User_Entity_Auth_Method::isMethodAvailable(Domain_User_Entity_Auth_Method::METHOD_MAIL)
				|| Domain_User_Entity_Auth_Method::isGuestMethodAvailable(Domain_User_Entity_Auth_Method::METHOD_MAIL))) {
			Domain_User_Entity_Mail::assertAlreadyExistMail($user_security);
		}
	}

	/**
	 * Завершить онбординг
	 *
	 *
	 * @throws BusFatalException
	 * @throws Domain_User_Exception_Onboarding_NotAllowedStatus
	 * @throws Domain_User_Exception_Onboarding_NotAllowedStatusStep
	 * @throws Domain_User_Exception_Onboarding_NotAllowedType
	 * @throws ReturnFatalException
	 * @throws ParseFatalException
	 * @throws EndpointAccessDeniedException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws cs_UserNotFound
	 */
	public static function finishOnboarding(int $user_id, string $type): void
	{

		$type = Domain_User_Entity_Onboarding::formatTypeToInt($type);
		Domain_User_Action_Onboarding_Finish::do($user_id, $type);
	}

	/**
	 * Запустить выбранный онбординг
	 *
	 * @throws BusFatalException
	 * @throws Domain_User_Exception_Onboarding_NotAllowedStatus
	 * @throws Domain_User_Exception_Onboarding_NotAllowedType
	 * @throws ReturnFatalException
	 * @throws ParseFatalException
	 * @throws EndpointAccessDeniedException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws cs_UserNotFound
	 * @throws \busException
	 * @throws cs_UserIsNotCreatorOfCompany
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 */
	public static function startOnboarding(int $user_id, string $type, int $space_id): void
	{

		$type = Domain_User_Entity_Onboarding::formatTypeToInt($type);

		// проверяем, что пользователь является создателем пространства
		$space = Domain_Company_Entity_Company::get($space_id);
		Domain_Company_Entity_Company::assertUserIsCreator($space, $user_id);

		// проверяем, может у пользователя уже есть активный или завершённый онбординг
		$user            = Gateway_Bus_PivotCache::getUserInfo($space->created_by_user_id);
		$onboarding_list = Type_User_Main::getOnboardingList($user->extra);

		foreach ($onboarding_list as $onboarding) {

			if (in_array($onboarding->status, [Domain_User_Entity_Onboarding::STATUS_ACTIVE, Domain_User_Entity_Onboarding::STATUS_FINISHED])) {
				return;
			}
		}

		$data = [
			"space_id" => $space_id,
		];

		Domain_User_Action_Onboarding_Activate::do($user, $type, $data);
	}

	/**
	 * получаем онлайн пользователя
	 */
	public static function getOnline(int $user_id): int
	{

		return Domain_User_Action_Online_Get::do($user_id);
	}

	/**
	 * получаем список онлайна пользователей
	 */
	public static function getOnlineList(array $user_id_list): array
	{

		return Domain_User_Action_Online_GetList::do($user_id_list);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Отдаем команду, если пустой профиль
	 *
	 * @throws \busException
	 * @throws cs_AnswerCommand
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \userAccessException
	 */
	protected static function _throwIfProfileIsEmpty(int $user_id): void
	{

		if (Type_User_Main::isEmptyProfile($user_id)) {
			throw new cs_AnswerCommand("need_fill_profile", []);
		}
	}

	/**
	 * получаем и возвращаем параметры подключения для WS у авторизованного юзера
	 *
	 *
	 * @throws \busException
	 * @throws cs_AnswerCommand
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \userAccessException
	 */
	protected static function _getWsTokenAndUrlAuthUser(int $user_id, string $device_id, string $platform): array
	{

		// проверяем, заполнен ли профиль
		Domain_User_Entity_User::throwCommandIfEmptyProfile($user_id);

		try {

			// получаем и возвращаем параметры подключения для WS
			[$ws_token, $ws_url] = Gateway_Bus_SenderBalancer::getConnection($user_id, $device_id, $platform);
		} catch (BusFatalException) {

			$ws_token = "";
			$ws_url   = "";
		} // игнорируем ошибку, чтобы не крашить метод doStart

		return [$ws_token, $ws_url];
	}

	/**
	 * получаем информацию о состоянии уведомлений в приложении у авторизованного юзера
	 */
	protected static function _getNotificationPreferencesAuthUser(int $user_id): array
	{

		// получаем информацию о состоянии уведомлений в приложении
		return Domain_User_Action_Notifications_GetPreferences::do($user_id);
	}

	/**
	 * получаем информацию о состоянии звонка у авторизованного юзера
	 */
	#[ArrayShape(["constants" => "array", "active_call" => "object"])]
	protected static function _getCallPreferencesAuthUser(int $user_id): array
	{

		// получаем информацию о состоянии звонка
		[$call_constants, $active_call] = Domain_User_Scenario_Api::getCallPreferences($user_id);

		$formatted_active_call = $active_call !== false ? Apiv1_Pivot_Format::getActiveCall($active_call) : [];

		return [
			"constants"   => (array) Apiv1_Pivot_Format::getCallConstants($call_constants),
			"active_call" => (object) $formatted_active_call,
		];
	}

	/**
	 * получаем информацию о настройках для бота
	 */
	protected static function _getUserbotPreferences(): array
	{

		return [
			"api_documentation_url" => (string) Domain_Userbot_Action_GetApiDocumentationUrl::do(),
		];
	}
}
