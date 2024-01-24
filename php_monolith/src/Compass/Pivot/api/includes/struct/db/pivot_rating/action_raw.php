<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_rating_{10m}.action_raw_list_{1}
 */
class Struct_Db_PivotRating_ActionRaw {

	/**
	 * Struct_Db_PivotRating_ActionRaw constructor
	 *
	 * @param int   $user_id
	 * @param int   $space_id
	 * @param int   $action_at
	 * @param int   $created_at
	 * @param array $action_list
	 */
	public function __construct(
		public int   $user_id,
		public int   $space_id,
		public int   $action_at,
		public int   $created_at,
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
			$arr["space_id"],
			$arr["action_at"],
			$arr["created_at"],
			fromJson($arr["action_list"]),
		);
	}
}