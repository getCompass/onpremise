<?php declare(strict_types = 1);

namespace Compass\Pivot;

/**
 * Api-сценарии для работы с выделенными решениями.
 */
class Domain_Solution_Scenario_Api {

	/**
	 * Пытается авторизовать пользователя по указанному токену.
	 *
	 * @param int    $user_id
	 * @param string $authentication_token
	 *
	 * @return array
	 * @throws cs_UserAlreadyBlocked
	 * @throws cs_UserAlreadyLoggedIn
	 * @throws cs_UserNotFound
	 */
	public static function tryAuthenticationToken(int $user_id, string $authentication_token):array {

		self::_throwIfPlatformIsProhibited();

		// валидируем токен
		$authentication_token_data = Domain_Solution_Entity_AuthenticationValidator::validate($authentication_token);
		$token_cache_key           = Domain_Solution_Action_GenerateAuthenticationToken::makeKey($authentication_token_data->user_id);

		try {

			// если уже авторизованы, то нужно инвалидировать токен
			// так, чисто на всякий случай, чтобы не болтался
			Domain_User_Entity_Validator::assertNotLoggedIn($user_id);
		} catch (cs_UserAlreadyLoggedIn $e) {

			Domain_Solution_Action_InvalidateAuthenticationKey::exec($token_cache_key);
			throw $e;
		}

		$user_id = $authentication_token_data->user_id;

		// проверяем, что пользователь не удалил аккаунт
		$user_info = Gateway_Bus_PivotCache::getUserInfo($user_id);
		if (Type_User_Main::isDisabledProfile($user_info->extra)) {
			throw new cs_UserAlreadyBlocked("user is already blocked");
		}

		// обновляем user_id для текущего девайса
		Type_User_Notifications::updateUserIdForDevice($user_id, getDeviceId());

		// чистим кэш и выдаем сессию
		Type_Session_Main::doLoginSession($user_id);
		Type_User_ActionAnalytics::sessionStart($user_id);

		Domain_Solution_Action_InvalidateAuthenticationKey::exec($token_cache_key);

		// TODO: Здесь нужно собрать аналитику, по аналогии с tryConfirm
		// TODO: возможно стоит вообще этот кусок вынести в отдельный action

		$join_link_info = [];
		if (mb_strlen($authentication_token_data->join_link_uniq) > 0) {
			$join_link_info = self::_getJoinLinkInfo($user_id, $authentication_token_data->join_link_uniq);
		}

		return [$user_id, $join_link_info];
	}

	/**
	 * Кидаем ошибку если с платформы запрещено работать
	 *
	 * @return void
	 * @long
	 */
	protected static function _throwIfPlatformIsProhibited():void {

		// получаем платформу
		try {
			$platform = Type_Api_Platform::getPlatform();
		} catch (cs_PlatformNotFound) {
			$platform = Type_Api_Platform::PLATFORM_OTHER;
		}

		switch ($platform) {

			case Type_Api_Platform::PLATFORM_ELECTRON:
			case Type_Api_Platform::PLATFORM_ELECTRON_OS_MACOS:
			case Type_Api_Platform::PLATFORM_ELECTRON_OS_WINDOWS:
			case Type_Api_Platform::PLATFORM_ELECTRON_OS_LINUX:

				if (Type_Restrictions_Platform::isDesktopProhibited()) {
					throw new Domain_App_Exception_Restrictions_PlatformProhibited("desktop platform is prohibited");
				}
				break;

			case Type_Api_Platform::PLATFORM_IOS:
			case Type_Api_Platform::PLATFORM_IPAD:

				if (Type_Restrictions_Platform::isIosProhibited()) {
					throw new Domain_App_Exception_Restrictions_PlatformProhibited("ios platform is prohibited");
				}
				break;

			case Type_Api_Platform::PLATFORM_ANDROID:

				if (Type_Restrictions_Platform::isAndroidProhibited()) {
					throw new Domain_App_Exception_Restrictions_PlatformProhibited("android platform is prohibited");
				}
				break;

			default:
				break;
		}
	}

	/**
	 * получаем данные по ссылке-приглашению
	 */
	protected static function _getJoinLinkInfo(int $user_id, string $join_link_uniq):array {

		try {
			$join_link_rel_row = Gateway_Db_PivotData_CompanyJoinLinkRel::get($join_link_uniq);
		} catch (\cs_RowIsEmpty) {
			return [];
		}

		// получаем данные по ссылке-приглашению для участника компании
		try {

			$validation_result = Domain_Link_Entity_Link::getJoinLinkInfoForMember($user_id, $join_link_rel_row);
			return Onpremiseweb_Format::joinLinkInfo($validation_result);
		} catch (\Exception) {
			return [];
		}
	}
}
