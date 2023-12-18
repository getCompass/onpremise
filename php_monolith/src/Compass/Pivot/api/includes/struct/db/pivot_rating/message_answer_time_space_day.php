<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_rating_{10m}.message_answer_time_raw_list_{1}
 */
class Struct_Db_PivotRating_MessageAnswerTimeSpaceDay {

	/**
	 * Struct_Db_PivotRating_MessageAnswerTimeSpaceDay constructor
	 *
	 * @param int   $space_id
	 * @param int   $day_start_at
	 * @param int   $created_at
	 * @param int   $updated_at
	 * @param array $answer_time_list
	 */
	public function __construct(
		public int   $space_id,
		public int   $day_start_at,
		public int   $created_at,
		public int   $updated_at,
		public array $answer_time_list
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
			$arr["day_start_at"],
			$arr["created_at"],
			$arr["updated_at"],
			fromJson($arr["answer_time_list"]),
		);
	}
}