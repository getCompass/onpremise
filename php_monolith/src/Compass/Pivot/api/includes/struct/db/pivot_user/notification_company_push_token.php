<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_user_{10m}.notification_company_push_token_{1}
 */
class Struct_Db_PivotUser_NotificationCompanyPushToken {

	public int    $user_id;
	public int    $company_id;
	public string $token_hash;
	public int    $created_at;
	public int    $updated_at;

	public function __construct(
		int    $user_id,
		int    $company_id,
		string $token_hash,
		int    $created_at,
		int    $updated_at
	) {

		$this->user_id    = $user_id;
		$this->company_id = $company_id;
		$this->token_hash = $token_hash;
		$this->created_at = $created_at;
		$this->updated_at = $updated_at;
	}
}