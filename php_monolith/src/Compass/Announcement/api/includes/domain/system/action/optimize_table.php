<?php

namespace Compass\Announcement;

/**
 * Класс оптимизации таблицы таблицы с токенами пользователей.
 */
class Domain_System_Action_OptimizeTable {

	/**
	 * Запускает оптимизацию таблиц,
	 * связанных с динамическими данными пользователей.
	 */
	public static function run():void {

		// допускам работу только через крон
		if (!isCron() || !isTestServer()) {
			return;
		}

		Gateway_Db_AnnouncementCompany_CompanyUser::optimize();
		Gateway_Db_AnnouncementUser_UserCompany::optimize();
		Gateway_Db_AnnouncementSecurity_TokenUser::optimize();
	}
}