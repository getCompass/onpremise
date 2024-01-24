<?php

namespace Compass\Pivot;

/**
 * Класс обработки ввода неверного смс при подтверждении 2fa
 *
 * Class Domain_User_Action_TwoFa_HandleWrongCode
 */
class Domain_User_Action_TwoFa_HandleWrongCode {

	/**
	 * действие регстрации
	 *
	 * @throws \parseException
	 */
	public static function do(Domain_User_Entity_TwoFa_Story $two_fa_story):void {

		$two_fa_map = $two_fa_story->getPhoneInfo()->two_fa_map;
		$updated_at = time();

		Gateway_Db_PivotAuth_TwoFaPhoneList::set($two_fa_map, [
			"error_count" => "error_count + 1",
			"updated_at"  => $updated_at,
		]);
	}
}
