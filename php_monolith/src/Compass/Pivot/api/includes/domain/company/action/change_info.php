<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Базовый класс для изменения данных компании
 */
class Domain_Company_Action_ChangeInfo {

	/**
	 * выполняем
	 *
	 * @param int          $company_id
	 * @param string|false $name
	 * @param string|false $avatar_file_map
	 *
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \parseException
	 */
	public static function do(int $company_id, string|false $name, string|false $avatar_file_map):void {

		$set = [];

		if ($name !== false) {
			$set["name"] = $name;
		}

		if ($avatar_file_map !== false) {
			$set["avatar_file_map"] = $avatar_file_map;
		}

		if (count($set) < 1) {
			throw new ParseFatalException("not exist data for update");
		}

		$set["updated_at"] = time();

		Gateway_Db_PivotCompany_CompanyList::set($company_id, $set);
	}
}
