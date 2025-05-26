<?php

namespace Compass\Pivot;

/**
 * Сценарии для работы с устройствами пользователя
 */
class Domain_User_Scenario_Api_Security_Device {

	/**
	 * Получить авторизованные устройства пользователя.
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\DBShardingNotFoundException
	 * @throws \BaseFrame\Exception\Gateway\QueryFatalException
	 * @throws \queryException
	 */
	public static function getAuthenticatedList(int $user_id, string $current_session_uniq):array {

		$session_active_list = Gateway_Db_PivotUser_SessionActiveList::getActiveSessionList($user_id);

		// сортируем полученные сессии по времени авторизации по убыванию
		usort($session_active_list, function(Struct_Db_PivotUser_SessionActive $a, Struct_Db_PivotUser_SessionActive $b) {

			return $b->login_at <=> $a->login_at;
		});

		$formatted_session_device_list = [];
		$current_session_device        = [];
		foreach ($session_active_list as $session_active) {

			// если устройство авторизовано на сайте, то пропускаем его
			if (Domain_User_Entity_SessionExtra::getLoginType($session_active->extra) == Domain_User_Entity_SessionExtra::ONPREMISE_WEB_LOGIN_TYPE) {
				continue;
			}

			// получаем публичный session_id для сессии устройства
			$public_session_id = Domain_User_Action_Security_Device_GetSessionId::doEncrypt($session_active->session_uniq);

			// для текущей сессии пользователя
			if ($session_active->session_uniq == $current_session_uniq) {

				$current_session_device = Apiv2_Format::sessionDevice($public_session_id, $session_active, true);
				continue;
			}

			$formatted_session_device_list[] = Apiv2_Format::sessionDevice($public_session_id, $session_active, false);
		}

		// текущее устройство добавляем в самое начало списка
		count($current_session_device) > 0 && array_unshift($formatted_session_device_list, $current_session_device);

		return $formatted_session_device_list;
	}

	/**
	 * Инвалидируем устройство пользователя
	 *
	 * @throws Domain_User_Exception_Security_Device_IncorrectSessionId
	 * @throws Domain_User_Exception_Security_Device_RecentlyLoginSession
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\DBShardingNotFoundException
	 * @throws \BaseFrame\Exception\Gateway\QueryFatalException
	 * @throws \parseException
	 */
	public static function invalidate(int $user_id, string $current_session_uniq, string $public_session_id):void {

		$session_uniq = Domain_User_Action_Security_Device_GetSessionId::doDecrypt($public_session_id);

		$session_active_list = Gateway_Db_PivotUser_SessionActiveList::getList($user_id, [$current_session_uniq, $session_uniq]);

		$session_for_logout = null;
		foreach ($session_active_list as $session) {

			// для текущей сессии проверяем, что сессия была авторизована давно
			if ($session->session_uniq == $current_session_uniq) {

				if (Domain_User_Action_Security_Device_IsRecentlyLoginSession::do($session->login_at)) {
					throw new Domain_User_Exception_Security_Device_RecentlyLoginSession("current used session is recently login");
				}
				continue;
			}

			$session_for_logout = $session;
		}

		if (is_null($session_for_logout)) {
			return;
		}

		// разлогиниваем сессию устройства пользователя
		Type_Session_Main::doLogoutDevice($user_id, $session_for_logout->session_uniq);

		// отправляем ws-событие с device_id устройства
		$device_id = Domain_User_Entity_SessionExtra::getDeviceId($session_for_logout->extra);
		mb_strlen($device_id) > 0 && Gateway_Bus_SenderBalancer::authenticatedDeviceLogout($user_id, [$device_id]);
	}

	/**
	 * Инвалидировать остальные устройства пользователя.
	 *
	 * @throws Domain_User_Exception_Security_Device_RecentlyLoginSession
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 * @throws \parseException
	 * @throws cs_IncorrectSaltVersion
	 */
	public static function invalidateOther(int $user_id, string $current_session_uniq):void {

		$session_active_list = Gateway_Db_PivotUser_SessionActiveList::getActiveSessionList($user_id);

		$filtered_active_session_list = [];
		$device_id_list               = [];
		foreach ($session_active_list as $session_active) {

			// если сессия принадлежит текущей сессии пользователя - пропускаем
			if ($session_active->session_uniq == $current_session_uniq) {

				if (Domain_User_Action_Security_Device_IsRecentlyLoginSession::do($session_active->login_at)) {
					throw new Domain_User_Exception_Security_Device_RecentlyLoginSession("current used session is recently login");
				}

				continue;
			}

			$filtered_active_session_list[] = $session_active;

			$device_id = Domain_User_Entity_SessionExtra::getDeviceId($session_active->extra);
			if (mb_strlen($device_id) > 0) {
				$device_id_list[] = $device_id;
			}
		}

		// разлогиниваем сессии устройств пользователя
		count($filtered_active_session_list) > 0 && Type_Session_Main::doLogoutDeviceList($user_id, $filtered_active_session_list);

		// отправляем ws-событие с device_id устройства
		count($device_id_list) > 0 && Gateway_Bus_SenderBalancer::authenticatedDeviceLogout($user_id, $device_id_list);
	}
}