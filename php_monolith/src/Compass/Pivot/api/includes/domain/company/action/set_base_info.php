<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Базовый класс для редактирования основных данных профиля компании
 */
class Domain_Company_Action_SetBaseInfo {

	/**
	 * выполняем
	 *
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function do(int $company_id, string|false $name, int|false $avatar_color_id):void {

		$set = [];

		if ($name !== false) {
			$set["name"] = $name;
		}

		if ($avatar_color_id !== false) {
			$set["avatar_color_id"] = $avatar_color_id;
		}

		if (count($set) < 1) {
			throw new ParseFatalException("not exist data for update");
		}

		$set["updated_at"] = time();

		Gateway_Db_PivotCompany_CompanyList::set($company_id, $set);
	}
}
