<?php

namespace Compass\Pivot;

/**
 * Класс для инвалидации 2fa токена
 *
 * Class Domain_User_Action_TwoFa_InvalidateToken
 */
class Domain_User_Action_TwoFa_InvalidateToken {

	/**
	 * действие инвалидации
	 *
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 */
	public static function do(Domain_User_Entity_Confirmation_TwoFa_Story $two_fa_story):void {

		$updated_at = time();
		$two_fa_map = $two_fa_story->getPhoneInfo()->two_fa_map;

		Gateway_Db_PivotAuth_TwoFaList::set($two_fa_map, [
			"is_active"  => 0,
			"is_success" => 1,
			"updated_at" => $updated_at,
		]);
	}
}
