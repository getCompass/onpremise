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

		// получаем всех пользователей в приложении
		$user_count = Gateway_Db_PivotUser_UserList::getUserCount();
		$user_list  = Gateway_Db_PivotUser_UserList::getAll($user_count, 0);

		// разбиваем на 100 пользователей, чтобы не убить коллектор
		$chunk_user_list = array_chunk($user_list, self::_COMPANY_USER_LIST_CHUNK);

		// для каждых 100 пользователей
		foreach ($chunk_user_list as $chunk) {

			foreach ($chunk as $user) {

				try {

					Domain_User_Scenario_Phphooker::onSendAccountStatusLog($user->user_id, Type_User_Analytics::CRON_UPDATE);
				} catch (\Exception $e) {
					Type_System_Admin::log("analytic-user-cron-log", $e);
				}
			}

			usleep(0.2 * 1000 * 1000);
		}
	}
}