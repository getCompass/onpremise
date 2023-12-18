<?php

namespace Compass\Company;

/**
 * Action для удаления компании
 */
class Domain_Company_Action_Delete {

	/**
	 * Удаляем компанию
	 *
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function do(int $deleted_at):void {

		// устанавливаем факт удаления
		try {
			Gateway_Db_CompanyData_CompanyDynamic::set("is_deleted_alias", ["value" => 1, "updated_at" => time()]);
		} catch (cs_RowNotUpdated) {

			Gateway_Db_CompanyData_CompanyDynamic::insert(new Struct_Db_CompanyData_CompanyDynamic(
				"is_deleted_alias",
				1,
				time(),
				0,
			));
		}

		// устанавливаем время удаления
		try {
			Gateway_Db_CompanyData_CompanyDynamic::set("deleted_at_alias", ["value" => $deleted_at, "updated_at" => time()]);
		} catch (cs_RowNotUpdated) {

			Gateway_Db_CompanyData_CompanyDynamic::insert(new Struct_Db_CompanyData_CompanyDynamic(
				"deleted_at_alias",
				$deleted_at,
				time(),
				0,
			));
		}

		// разлогиниваем всех участников
		Gateway_Db_CompanyData_SessionActiveList::truncate();
		Gateway_Bus_CompanyCache::clearSessionCache();
	}
}
