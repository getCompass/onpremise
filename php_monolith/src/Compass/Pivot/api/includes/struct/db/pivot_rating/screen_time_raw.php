<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_rating_{10m}.screen_time_raw_list_{1}
 */
class Struct_Db_PivotRating_ScreenTimeRaw {

	/**
	 * Struct_Db_PivotRating_ScreenTimeRaw constructor
	 *
	 * @param int    $user_id
	 * @param int    $space_id
	 * @param string $user_local_time
	 * @param int    $screen_time
	 * @param int    $created_at
	 */
	public function __construct(
		public int    $user_id,
		public int    $space_id,
		public string $user_local_time,
		public int    $screen_time,
		public int    $created_at
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
			$arr["space_id"],
			$arr["user_local_time"],
			$arr["screen_time"],
			$arr["created_at"],
		);
	}
}