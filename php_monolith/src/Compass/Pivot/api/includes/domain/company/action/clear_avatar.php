<?php

namespace Compass\Pivot;

/**
 * Базовый класс для очистки аватара компании
 */
class Domain_Company_Action_ClearAvatar {

	/**
	 * чистим аватар компании
	 *
	 * @param int $company_id
	 *
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \parseException
	 */
	public static function do(int $company_id):void {

		// чистим аватар
		$set = [
			"avatar_file_map" => "",
			"updated_at"      => time(),
		];
		Gateway_Db_PivotCompany_CompanyList::set($company_id, $set);
	}
}