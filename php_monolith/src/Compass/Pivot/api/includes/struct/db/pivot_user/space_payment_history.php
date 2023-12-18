<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_user_{10m}.space_payment_history_{1}
 */
class Struct_Db_PivotUser_SpacePaymentHistory {

	public function __construct(
		public null|int $id,
		public int      $user_id,
		public int      $space_id,
		public int      $tariff_plan_id,
		public string   $payment_id,
		public int      $payment_at,
		public int      $created_at,
		public int      $updated_at,
	) {
	}

	/**
	 * конвертируем запись в структуру
	 *
	 * @return Struct_Db_PivotUser_SpacePaymentHistory
	 */
	public static function rowToStruct(array $row):Struct_Db_PivotUser_SpacePaymentHistory {

		return new Struct_Db_PivotUser_SpacePaymentHistory(
			(int) $row["id"],
			(int) $row["user_id"],
			(int) $row["space_id"],
			(int) $row["tariff_plan_id"],
			(string) $row["payment_id"],
			(int) $row["payment_at"],
			(int) $row["created_at"],
			(int) $row["updated_at"],
		);
	}
}