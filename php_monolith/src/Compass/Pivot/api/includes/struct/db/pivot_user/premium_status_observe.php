<?php

namespace Compass\Pivot;

/**
 * Класс-структура для таблицы pivot_user_{n}m.premium_status_observe
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Db_PivotUser_PremiumStatusObserve extends Struct_Default {

	public function __construct(
		public int $id,
		public int $user_id,
		public int $observe_at,
		public int $action,
		public int $created_at,
	) {

	}
}
