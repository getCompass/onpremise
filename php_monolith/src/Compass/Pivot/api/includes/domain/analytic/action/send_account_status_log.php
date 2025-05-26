<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * Действие отправки лога текущего статуса всех пользователей
 */
class Domain_Analytic_Action_SendAccountStatusLog {

	protected const _COMPANY_USER_LIST_CHUNK = 10;

	/**
	 * выполняем
	 */
	public static function do():void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$offset = 0;
		do {

			// разбиваем на 10 пользователей, чтобы не убить коллектор
			$user_list = Gateway_Db_PivotUser_UserList::getAll(self::_COMPANY_USER_LIST_CHUNK, $offset);
			$offset    += count($user_list);

			foreach ($user_list as $user) {

				try {

					Domain_User_Scenario_Phphooker::onSendAccountStatusLog($user->user_id, Type_User_Analytics::CRON_UPDATE);
				} catch (\Exception $e) {
					Type_System_Admin::log("analytic-user-cron-log", $e);
				}
			}

			usleep(0.2 * 1000 * 1000);
		} while (count($user_list) > 0);
	}
}