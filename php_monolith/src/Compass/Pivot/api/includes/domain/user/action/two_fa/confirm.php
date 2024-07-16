<?php

namespace Compass\Pivot;

/**
 * Класс подтверждения 2fa смс
 *
 * Class Domain_User_Action_TwoFa_Confirm
 */
class Domain_User_Action_TwoFa_Confirm {

	/**
	 * действие подтверждения
	 *
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(Domain_User_Entity_Confirmation_TwoFa_Story $two_fa_story):void {

		$shard_id   = Type_Pack_TwoFa::getShardIdByTime($two_fa_story->getPhoneInfo()->created_at);
		$two_fa_map = $two_fa_story->getPhoneInfo()->two_fa_map;
		$updated_at = time();

		Gateway_Db_PivotAuth_Main::beginTransaction($shard_id);

		Gateway_Db_PivotAuth_TwoFaPhoneList::set($two_fa_map, [
			"is_success" => 1,
			"updated_at" => $updated_at,
		]);

		Gateway_Db_PivotAuth_TwoFaList::set($two_fa_map, [
			"is_active"  => 1,
			"updated_at" => $updated_at,
		]);

		Gateway_Db_PivotAuth_Main::commitTransaction($shard_id);
	}
}
