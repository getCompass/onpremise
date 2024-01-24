<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_rating_{10m}.screen_time_space_day_list_{1}
 */
class Struct_Db_PivotRating_ScreenTimeSpaceDay {

	/**
	 * Struct_Db_PivotRating_ScreenTimeSpaceDay constructor
	 *
	 * @param int    $space_id
	 * @param string $user_local_date
	 * @param int    $created_at
	 * @param int    $updated_at
	 * @param array  $screen_time_list
	 */
	public function __construct(
		public int    $space_id,
		public string $user_local_date,
		public int    $created_at,
		public int    $updated_at,
		public array  $screen_time_list
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
			$arr["space_id"],
			$arr["user_local_date"],
			$arr["created_at"],
			$arr["updated_at"],
			fromJson($arr["screen_time_list"]),
		);
	}
}