<?php

namespace Compass\Pivot;

/**
 * Базовый класс для изменения цвета аватара компании
 */
class Domain_Company_Action_SetAvatar {

	/**
	 * Редактируем цвета аватара
	 *
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function do(int $company_id, int $avatar_color_id):void {

		Gateway_Db_PivotCompany_CompanyList::set($company_id, [
			"avatar_color_id" => $avatar_color_id,
		]);
	}
}
