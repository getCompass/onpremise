<?php

namespace Compass\Company;

/**
 * Класс для описания структуры общего рейтинга
 */
class Struct_Bus_Rating_General {

	public int  $year;
	public ?int $week  = null;
	public ?int $month = null;
	public int  $count;
	public int  $updated_at;

	/** @var Struct_Bus_Rating_General_TopItem[] $top_list */
	public array $top_list;
	public int   $has_next;

	// -------------------------------------------------------
	// PUBLIC
	// -------------------------------------------------------

	/**
	 * Получить id пользователей топ листа
	 */
	public function getTopUserIdList():array {

		$user_id_list = [];
		foreach ($this->top_list as $v) {
			$user_id_list[$v->user_id] = $v->user_id;
		}

		return array_keys($user_id_list);
	}
}
