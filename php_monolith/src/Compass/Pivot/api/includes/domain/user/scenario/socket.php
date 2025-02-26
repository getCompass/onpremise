<?php

namespace Compass\Pivot;

/**
 * Сценарии для сокет методов
 */
class Domain_User_Scenario_Socket {

	/**
	 * Сценарий генерации 2fa токена
	 *
	 * @param int $user_id
	 * @param int $company_id
	 * @param int $action_type
	 *
	 * @return Struct_Db_PivotAuth_TwoFa
	 * @throws cs_TwoFaTypeIsInvalid
	 * @throws \parseException
	 * @throws \queryException|cs_blockException
	 */
	public static function doGenerateTwoFaToken(int $user_id, int $company_id, int $action_type):Struct_Db_PivotAuth_TwoFa {

		Domain_User_Entity_Confirmation_Main::assertTypeIsValid($action_type);

		try {
			// пробуем достать предыдущий запрос
			$two_fa_story = Domain_User_Entity_Confirmation_TwoFa_TwoFa::getLastByUserAndType($user_id, $action_type, $company_id);
			$two_fa_story->assertNotExpired()
				->assertNotFinished()
				->assertNotActive();
		} catch (\cs_RowIsEmpty|cs_TwoFaIsExpired|cs_TwoFaIsFinished|cs_TwoFaIsActive) {

			self::_checkAntispamIfNeed($user_id, $action_type);
			$two_fa_data  = Domain_User_Action_TwoFa_GenerateToken::do($user_id, $action_type, $company_id);
			$two_fa_story = new Domain_User_Entity_Confirmation_TwoFa_TwoFa($two_fa_data);

			// добавляем в phphooker задачу, чтобы в случае протухания попытки – скинуть лог в аналитику
			Type_Phphooker_Main::onTwoFaStoryExpire($two_fa_data->two_fa_map, $two_fa_data->expires_at);
		}

		return $two_fa_story->getData();
	}

	/**
	 * Проверяем антиспам если необходимо
	 *
	 * @param int $user_id
	 * @param int $action_type
	 *
	 * @return void
	 * @throws cs_blockException
	 */
	protected static function _checkAntispamIfNeed(int $user_id, int $action_type):void {

		switch ($action_type) {

			case Domain_User_Entity_Confirmation_Main::CONFIRMATION_SELF_DISMISSAL_TYPE:
				Type_Antispam_User::throwIfBlocked($user_id, Type_Antispam_User::CONFIRMATION_SELF_DISMISSAL_TYPE);
				break;

			default:
				break;
		}
	}

	/**
	 * Сценарий валидации 2fa токена
	 *
	 * @param int    $user_id
	 * @param int    $company_id
	 * @param int    $action_type
	 * @param string $two_fa_key
	 *
	 * @throws \blockException
	 * @throws \cs_DecryptHasFailed
	 * @throws cs_TwoFaInvalidCompany
	 * @throws cs_TwoFaInvalidUser
	 * @throws cs_TwoFaIsExpired
	 * @throws cs_TwoFaIsFinished
	 * @throws cs_TwoFaIsNotActive
	 * @throws cs_TwoFaTypeIsInvalid
	 * @throws cs_UnknownKeyType
	 * @throws \cs_UnpackHasFailed
	 * @throws cs_WrongTwoFaKey
	 */
	public static function tryValidateTwoFaToken(int $user_id, int $company_id, int $action_type, string $two_fa_key):void {

		// проверяем не заблокирована ли проверка
		Type_Antispam_Company::check($company_id, Type_Antispam_Company::WRONG_TWO_FA_TOKEN);

		try {

			$two_fa_map = Type_Pack_Main::replaceKeyWithMap("two_fa_key", $two_fa_key);
			$story      = Domain_User_Entity_Confirmation_TwoFa_Story::getByMap($two_fa_map);

			$story->assertCorrectUser($user_id)
				->assertCorrectCompanyId($company_id)
				->assertTypeIsValid($action_type)
				->assertNotExpired()
				->assertNotFinished()
				->assertActive();
		} catch (\Exception $e) {

			Type_Antispam_Company::checkAndIncrementBlock($company_id, Type_Antispam_Company::WRONG_TWO_FA_TOKEN);
			throw $e;
		}
	}

	/**
	 * Сценарий инвалидации 2fa токена
	 *
	 * @param int    $user_id
	 * @param string $two_fa_map
	 *
	 * @throws cs_TwoFaInvalidUser
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 */
	public static function setTwoFaTokenAsInactive(int $user_id, string $two_fa_map):void {

		try {

			$story = Domain_User_Entity_Confirmation_TwoFa_Story::getByMap($two_fa_map);
			$story->assertCorrectUser($user_id);

			Domain_User_Action_TwoFa_InvalidateToken::do($story);
		} catch (cs_WrongTwoFaKey) {
			// подавляем exception, инвалидация не должна выбрасывать ошибки
		}
	}

	/**
	 * Получает пользователя
	 *
	 * @param int $user_id
	 * @param int $company_id
	 *
	 * @return Struct_Db_PivotUser_User
	 *
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \userAccessException
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public static function getUserInfo(int $user_id, int $company_id):Struct_Db_PivotUser_User {

		// получаем список пользователей
		$user_info_list = Domain_Company_Action_GetUserList::do($company_id, [$user_id]);
		if (!isset($user_info_list[$user_id])) {
			throw new \cs_RowIsEmpty();
		}
		return $user_info_list[$user_id];
	}

	/**
	 * Получает список пользователей
	 *
	 * @param array $user_id_list
	 * @param int   $company_id
	 *
	 * @return Struct_Db_PivotUser_User[]
	 *
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \userAccessException
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public static function getUserInfoList(array $user_id_list, int $company_id):array {

		// получаем список пользователей
		return Domain_Company_Action_GetUserList::do($company_id, $user_id_list);
	}

	/**
	 * Сценарий обновления токена компании
	 *
	 * @param int    $user_id
	 * @param string $token
	 * @param string $device_id
	 * @param int    $company_id
	 * @param int    $is_add
	 *
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function setUserCompanyToken(int $user_id, string $token, string $device_id, int $company_id, int $is_add):void {

		if ($is_add) {

			Domain_User_Action_Notifications_AddUserCompanyToken::do($user_id, $device_id, $token, $company_id);
		} else {

			Domain_User_Action_Notifications_DeleteUserCompanyToken::do($user_id, $device_id, $token, $company_id);
		}
	}

	/**
	 * получаем активные звонки нескольких пользователей
	 */
	public static function getUserListActiveLastCall(array $user_id_list):array {

		$user_busy_call_list = Gateway_Db_PivotUser_UserLastCall::getListActive($user_id_list);

		$output = [];
		foreach ($user_busy_call_list as $user_busy_call_row) {
			$output[$user_busy_call_row->call_key][] = $user_busy_call_row->user_id;
		}

		return $output;
	}

	/**
	 * получаем записи о последнем звонке нескольких пользователей
	 *
	 * @param array $user_id_list
	 *
	 * @return Struct_Db_PivotUser_UserLastCall[]
	 */
	public static function getUserListLastCall(array $user_id_list):array {

		return Gateway_Db_PivotUser_UserLastCall::getList($user_id_list);
	}

	/**
	 * получаем все активные звонки
	 *
	 * @param int $company_id
	 *
	 * @return Struct_Db_PivotUser_UserLastCall[]
	 * @throws \parseException
	 */
	public static function getAllActiveCalls(int $company_id):array {

		return Gateway_Db_PivotUser_UserLastCall::getAllActive($company_id);
	}

	/**
	 * получаем последний звонок пользователя
	 *
	 * @param int $user_id
	 * @param int $company_id
	 *
	 * @return Struct_Db_PivotUser_UserLastCall|false
	 */
	public static function getUserLastCall(int $user_id, int $company_id):Struct_Db_PivotUser_UserLastCall|false {

		try {

			$last_call_row = Gateway_Db_PivotUser_UserLastCall::getOne($user_id);
		} catch (\cs_RowIsEmpty) {
			return false;
		}

		if ($last_call_row->company_id != $company_id) {
			return false;
		}
		return $last_call_row;
	}

	/**
	 * обновляем последний звонок
	 *
	 * @param array  $user_id_list
	 * @param string $call_key
	 * @param int    $is_finished
	 * @param int    $company_id
	 */
	public static function setLastCall(array $user_id_list, string $call_key, int $is_finished, int $company_id):void {

		$time = time();

		foreach ($user_id_list as $user_id) {

			$set = [
				"user_id"     => $user_id,
				"is_finished" => $is_finished,
				"company_id"  => $company_id,
				"updated_at"  => $time,
				"call_key"    => $call_key,
			];

			if (!$is_finished) {
				$set["created_at"] = $time;
			}

			Gateway_Db_PivotUser_UserLastCall::insertOrUpdate($user_id, $set);
		}
	}

	/**
	 * пытаемся пометить занятой телефонную линию пользователей
	 *
	 * @throws cs_OneOfUsersHaveActiveCall
	 */
	public static function tryMarkCallLineAsBusy(array $user_id_list, string $call_key, int $company_id):void {

		// проверяем занятость телефонной линии переданных пользователей
		$user_last_call_list      = Gateway_Db_PivotUser_UserLastCall::getList($user_id_list);
		$user_list_with_busy_line = [];
		foreach ($user_last_call_list as $user_last_call) {

			// если пользователь имеет занятую линию для стороннего звонка
			if ($user_last_call->is_finished === 0 && $user_last_call->call_key !== $call_key && $user_last_call->company_id !== $company_id) {
				$user_list_with_busy_line[] = $user_last_call->user_id;
			}
		}

		// если имеются пользователи с занятой телефонной линией
		if (count($user_list_with_busy_line) > 0) {
			throw new cs_OneOfUsersHaveActiveCall($user_list_with_busy_line);
		}

		// на этом этапе ни у кого телефонная линия не занята – значит занимаем ее для звонка
		Gateway_Db_PivotUser_UserLastCall::markCallLineAsBusyForUserList($user_id_list, $call_key, $company_id);
	}

	/**
	 * Обновить счетчик непрочитанных сообщений пользователю
	 *
	 * @param int   $user_id
	 * @param int   $company_id
	 * @param int   $messages_unread_count
	 * @param int   $inbox_unread_count
	 * @param array $conversation_key_list
	 * @param array $thread_key_list
	 *
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function updateBadgeCount(int $user_id, int $company_id, int $messages_unread_count, int $inbox_unread_count, array $conversation_key_list, array $thread_key_list):void {

		// обновляем количество непрочитанных сообщений
		Domain_User_Action_Company_SetUnreadCount::do($user_id, $company_id, $messages_unread_count, $inbox_unread_count);

		$device_list          = Domain_User_Action_Notifications_GetDeviceList::do($user_id);
		$company_id_list      = [];
		$filtered_device_list = [];

		// смотрим список список ид компаний, по которым нужно посчитать количество непрочитанных сообщений
		foreach ($device_list as $device) {

			$extra_company_id_list = Type_User_Notifications_DeviceExtra::getCompanyIdList($device["extra"]);
			if (!in_array($company_id, $extra_company_id_list)) {

				continue;
			}

			$filtered_device_list[] = $device;
			$company_id_list        = array_merge($company_id_list, $extra_company_id_list);
		}

		// делаем отдельный массив company_id, чтобы достать из базы количество непрочитанных сообщений
		$company_id_list = array_unique($company_id_list);

		// получаем список записей с счетчиком непрочитанных сообщений
		$unread_count_list = $company_id_list !== [] ? Gateway_Db_PivotUser_CompanyInbox::getByCompanyList($user_id, $company_id_list) : [];

		// суммируем количество непрочитанных сообщений для каждого девайса
		foreach ($filtered_device_list as $device) {

			$total_unread_count    = 0;
			$extra_company_id_list = Type_User_Notifications_DeviceExtra::getCompanyIdList($device["extra"]);
			foreach ($extra_company_id_list as $company_id) {

				$total_unread_count += isset($unread_count_list[$company_id]) ? $unread_count_list[$company_id]->inbox_unread_count : 0;
			}

			if (count(Type_User_Notifications_DeviceExtra::getTokenList($device["extra"])) > 0) {

				// отправляем в пушер инфу об обновлении баджа
				Gateway_Bus_Pusher::updateBadgeCount($device, $total_unread_count, $conversation_key_list, $thread_key_list);
			}
		}
	}

	/**
	 * получаем данные для партнёрки
	 */
	public static function getInfoForPartner(array $user_id_list):array {

		$output = [];
		foreach ($user_id_list as $user_id) {

			try {

				$phone_number_obj = Domain_User_Scenario_Api::getPhoneNumberInfo($user_id);

				$country_code = $phone_number_obj->countryCode();
				$country      = \BaseFrame\Conf\Country::get($country_code);
				$country_name = $country->name;
			} catch (cs_UserPhoneSecurityNotFound|\BaseFrame\Exception\Domain\InvalidPhoneNumber) {

				$phone_number_obj = null;
				$country_name     = "";
				$country_code     = "";
			}

			$obfuscated_phone_number = !is_null($phone_number_obj) ? $phone_number_obj->obfuscate() : "-";

			$output[$user_id] = [
				"phone_number"       => $obfuscated_phone_number,
				"phone_country_name" => $country_name,
				"phone_country_code" => $country_code,
			];
		}

		return $output;
	}

	/**
	 * Получаем ссылки на все разрешения аватара
	 *
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \busException
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \userAccessException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getUserAvatarFileLinkList(array $user_id_list):array {

		// получаем информацию о пользователе
		$user_info_list = Gateway_Bus_PivotCache::getUserListInfo($user_id_list);

		$file_map_list_by_user_id = [];
		foreach ($user_info_list as $user_info) {

			if (mb_strlen($user_info->avatar_file_map) > 0) {
				$file_map_list_by_user_id[$user_info->user_id] = $user_info->avatar_file_map;
			}
		}

		if (count($file_map_list_by_user_id) < 1) {
			return [];
		}

		// получаем файлы
		$file_list = Gateway_Socket_PivotFileBalancer::getFileList(array_values($file_map_list_by_user_id));

		// собираем ответ
		$output = [];
		foreach ($file_list as $file_item) {

			$user_id = array_search($file_item["file_map"], $file_map_list_by_user_id);
			unset($file_map_list_by_user_id[$user_id]);

			$image_version_list["original"] = $file_item["url"];
			foreach ($file_item["data"]["image_version_list"] as $image_version) {

				$image_version_list["w" . $image_version["width"]] = $image_version["url"];
			}
			$output[$user_id] = $image_version_list;
		}

		return $output;
	}

	/**
	 * Проверяем, что пользователь может платить
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function isAllowBatchPay(int $user_id, int $company_id):bool {

		// получаем компанию
		$company = Domain_Company_Entity_Company::get($company_id);

		// получаем статус пользователя в компании
		[$is_admin,] = Gateway_Socket_Company::getInfoForPurchase($user_id, $company);

		return $is_admin;
	}

	/**
	 * Получаем статистику по экранному времени пользователя
	 *
	 * @param int $user_id
	 * @param int $days_count
	 *
	 * @return array
	 */
	public static function getScreenTimeStat(int $user_id, int $days_count):array {

		// формируем список дней за которые нужно получить
		$day_list = [];
		for ($i = 0; $i < $days_count; $i++) {
			$day_list[] = date(DATE_FORMAT_SMALL, dayStart() - DAY1 * $i);
		}

		// получаем статистику
		$row_list = Gateway_Db_PivotRating_ScreenTimeUserDayList::getByUserIdAndUserLocalDateList($user_id, $day_list);

		// форматируем для ответа
		$output = [];
		foreach ($row_list as $item) {

			if (!isset($output[$item->user_local_date])) {
				$output[$item->user_local_date] = [];
			}
			$output[$item->user_local_date] = $item->screen_time_list;
		}

		// сортируем по убыванию
		krsort($output);

		return $output;
	}

	/**
	 * Получаем список компаний в которых состоят как user_id_1, так и user_id_2
	 *
	 * @return array
	 */
	public static function getUsersIntersectSpaces(int $user_id_1, int $user_id_2):array {

		$user_1_company_id_list = array_column(Gateway_Db_PivotUser_CompanyList::getCompanyList($user_id_1), "company_id");
		$user_2_company_id_list = array_column(Gateway_Db_PivotUser_CompanyList::getCompanyList($user_id_2), "company_id");

		return array_values(array_intersect($user_1_company_id_list, $user_2_company_id_list));
	}

	/**
	 * Инкрементим статистику участия пользователя в конференции
	 *
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	public static function incConferenceMembershipRating(int $user_id, int $space_id):void {

		// если пользователь не участник пространства
		if (!Domain_Company_Entity_User_Member::isMember($user_id, $space_id)) {
			return;
		}

		// получаем информаицю о пространстве
		try {
			$space = Domain_Company_Entity_Company::get($space_id);
		} catch (cs_CompanyNotExist|cs_CompanyIncorrectCompanyId) {
			return;
		}

		Gateway_Socket_Company::incConferenceMembershipRating($space, $user_id);
	}

	/**
	 * Блокируем пользователю возможность аутентифицироваться в приложении
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function blockUserAuthentication(int $user_id):void {

		// завершаем все активные сессии на пивоте и в командах
		Type_Session_Main::clearAllUserPivotAndCompanySessions($user_id);

		// запрещаем авторизовываться, помечая профиль заблокированным
		Domain_User_Action_DisableProfile::do($user_id);

		// очищаем девайсы и токены для пользователя
		Type_User_Notifications::deleteDevicesForUser($user_id);
	}

	/**
	 * Разблокируем пользователю возможность аутентифицироваться в приложении
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function unblockUserAuthentication(int $user_id):void {

		// запрещаем авторизовываться, помечая профиль заблокированным
		Domain_User_Action_EnableProfile::do($user_id);
	}

	/**
	 * Получить список истории логина/разлогина устройств пользователя
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Gateway\DBShardingNotFoundException
	 * @throws \BaseFrame\Exception\Gateway\QueryFatalException
	 */
	public static function getDeviceLoginHistory(int $user_id):array {

		if ($user_id < 1) {
			return [];
		}

		try {
			$user_info = Gateway_Bus_PivotCache::getUserInfo($user_id);
		} catch (cs_UserNotFound) {
			return [];
		}

		$all_session_list = [];

		// получаем активные сессии устройств пользователя
		$session_active_list = Gateway_Db_PivotUser_SessionActiveList::getActiveSessionList($user_id);

		$all_session_list = array_merge($all_session_list, $session_active_list);

		// достаём записи пользователя из истории
		foreach (range(date("Y", $user_info->created_at), date("Y", time())) as $shard_id) {

			$session_history_list = Gateway_Db_PivotHistoryLogs_SessionHistory::getListByShardAndUserId($shard_id, $user_id);
			$all_session_list     = array_merge($all_session_list, $session_history_list);
		}

		return $all_session_list;
	}

	/**
	 * валидация pivot-сессии пользователя
	 *
	 * @return string
	 * @throws \cs_DecryptHasFailed
	 */
	public static function validateSession(string $pivot_session):string {

		// проверяем, начинается ли строка с "Bearer "
		if (str_starts_with($pivot_session, "Bearer ")) {

			// убираем "Bearer " из начала строки
			$pivot_session = preg_replace("/^Bearer\s+/", "", $pivot_session);

			// декодируем оставшуюся строку из base64
			$pivot_session_key = base64_decode($pivot_session);
		} else {
			$pivot_session_key = urldecode(urldecode($pivot_session));
		}

		// проверяем, что session_key валиден
		$pivot_session_map = Type_Pack_PivotSession::doDecrypt($pivot_session_key);

		// достаем session_uniq
		return Type_Pack_PivotSession::getSessionUniq($pivot_session_map);
	}
}
