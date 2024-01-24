<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_rating_{10m}.action_user_day_list_{1}
 */
class Struct_Db_PivotRating_ActionUserDay {

	/**
	 * Struct_Db_PivotRating_ActionUserDay constructor
	 *
	 * @param int   $user_id
	 * @param int   $day_start_at
	 * @param int   $created_at
	 * @param int   $updated_at
	 * @param array $action_list
	 */
	public function __construct(
		public int   $user_id,
		public int   $day_start_at,
		public int   $created_at,
		public int   $updated_at,
		public array $action_list
	) {
	}

	/**
	 * Формируем объект из массива
	 *
	 * @param array $arr
	 *
	 * @return static
	 */
	public static function fromRow(array $arr):self {

		return new self(
			$arr["user_id"],
			$arr["day_start_at"],
			$arr["created_at"],
			$arr["updated_at"],
			fromJson($arr["action_list"]),
		);
	}
}