<?php

namespace Compass\Pivot;

/**
 * Класс-структура для таблицы pivot_user_{n}m.premium_status_{m}
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Db_PivotUser_PremiumStatus extends Struct_Default {

	public function __construct(
		public int    $user_id,
		public bool   $need_block_if_inactive,
		public int    $status,
		public int    $free_active_till,
		public int    $active_till,
		public int    $created_at,
		public int    $updated_at,
		public int    $last_prolongation_at,
		public int    $last_prolongation_duration,
		public int    $last_prolongation_user_id,
		public string $last_prolongation_payment_id,
		public array  $extra,
	) {

	}
}
