<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_rating_{10m}.message_answer_time_raw_list_{1}
 */
class Struct_Db_PivotRating_MessageAnswerTimeRaw {

	/**
	 * Struct_Db_PivotRating_MessageAnswerTimeRaw constructor
	 *
	 * @param int    $user_id
	 * @param int    $answer_at
	 * @param string $conversation_key
	 * @param int    $answer_time
	 * @param int    $space_id
	 */
	public function __construct(
		public int    $user_id,
		public int    $answer_at,
		public string $conversation_key,
		public int    $answer_time,
		public int    $space_id
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
			$arr["answer_at"],
			$arr["conversation_key"],
			$arr["answer_time"],
			$arr["space_id"]
		);
	}
}