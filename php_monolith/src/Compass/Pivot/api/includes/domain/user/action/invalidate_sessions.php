<?php

namespace Compass\Pivot;

/**
 * Действие для инвалидации сессий пользователя на pivot сервере
 */
class Domain_User_Action_InvalidateSessions {

	/**
	 * выполняем
	 */
	public static function do(int $user_id):void {

		Type_System_Admin::log("user-kicker", "удаляю активные сессии пользователя {$user_id}");

		// сбрасываем сессии
		$session_list = Gateway_Db_PivotUser_SessionActiveList::getActiveSessionList($user_id);

		foreach ($session_list as $session) {

			Gateway_Db_PivotUser_SessionActiveList::delete($user_id, $session->session_uniq);
			Type_User_ActionAnalytics::sessionEnd($user_id);
		}

		// не забываем сбросить кэш
		Gateway_Bus_PivotCache::clearSessionCacheByUserId($user_id);
	}
}
