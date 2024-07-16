<?php

namespace Compass\Pivot;

/**
 * Класс для генерации 2fa токена
 *
 * Class Domain_User_Action_TwoFa_GenerateToken
 */
class Domain_User_Action_TwoFa_GenerateToken {

	/**
	 * действие генерации
	 *
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function do(int $user_id, int $action_type, int $company_id = 0):Struct_Db_PivotAuth_TwoFa {

		$two_fa_uniq = generateUUID();
		$time        = time();
		$expire_at   = $time + Domain_User_Entity_Confirmation_TwoFa_TwoFa::EXPIRE_AT;

		$shard_id   = Type_Pack_TwoFa::getShardIdByTime($time);
		$table_id   = Type_Pack_TwoFa::getTableIdByTime($time);
		$two_fa_map = Type_Pack_TwoFa::doPack($two_fa_uniq, $shard_id, $table_id, $time);

		$two_fa = new Struct_Db_PivotAuth_TwoFa(
			$two_fa_map,
			$user_id,
			$company_id,
			0,
			0,
			$action_type,
			$time,
			$time,
			$expire_at
		);

		Gateway_Db_PivotAuth_TwoFaList::insert($two_fa);

		return $two_fa;
	}
}
