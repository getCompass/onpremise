<?php

namespace Compass\Pivot;

/**
 * Класс-структура для таблицы pivot_user_{n}m.premium_prolongation_history_{m}
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Db_PivotUser_PremiumProlongationHistory extends Struct_Default {

	public function __construct(
		public int    $id,
		public int    $user_id,
		public int    $action,
		public int    $created_at,
		public int    $duration,
		public int    $active_till,
		public int    $doer_user_id,
		public string $payment_id,
		public array  $extra,
	) {

	}
}
