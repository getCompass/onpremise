<?php

namespace Compass\Pivot;

/**
 * Базовый класс для редактирования названия компаний
 */
class Domain_Company_Action_SetName {

	/**
	 * Изменяем имя
	 *
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function do(int $company_id, string $name):void {

		Gateway_Db_PivotCompany_CompanyList::set($company_id, [
			"name" => $name,
		]);
	}
}
