<?php

namespace Compass\Company;

/**
 * Экшн для логаута всех из компании
 */
class Domain_System_Action_LogoutAll {

	/**
	 * Разлогиниваем всех в компании
	 *
	 * @throws \busException|\parseException
	 */
	public static function do():array {

		$user_id_list = self::_getActiveUserIdList();

		// удаляем сессии
		Gateway_Db_CompanyData_SessionActiveList::truncate();

		foreach ($user_id_list as $user_id) {

			// чистим кэш
			Gateway_Bus_CompanyCache::clearSessionCacheByUserId($user_id);
		}

		return $user_id_list;
	}

	/**
	 * Получаем полный список активных пользователей
	 */
	protected static function _getActiveUserIdList():array {

		$user_id_list = [];
		$offset       = 0;
		$limit        = 1000;
		do {

			$user_list    = Gateway_Db_CompanyData_MemberList::getAllActiveMember($limit, $offset);
			$user_id_list = array_merge($user_id_list, array_column($user_list, "user_id"));

			if (count($user_list) == $limit) {

				$has_next = 1;
				$offset   += $limit;
				continue;
			}
			$has_next = 0;
		} while ($has_next);

		return $user_id_list;
	}
}