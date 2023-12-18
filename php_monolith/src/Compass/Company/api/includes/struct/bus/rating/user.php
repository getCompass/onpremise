<?php

namespace Compass\Company;

/**
 * Класс для описания структуры рейтинга по пользователю
 */
class Struct_Bus_Rating_User {

	public int   $user_id;
	public int   $general_position;
	public int   $year;
	public int   $week;
	public array $event_count_list;
	public int   $general_count;
	public int   $updated_at;

	/**
	 * Создаем дефолтную структуру Bus_Rating_User
	 */
	public function __construct() {

		$this->user_id          = 0;
		$this->year             = 0;
		$this->week             = 0;
		$this->general_count    = 0;
		$this->general_position = 0;
		$this->updated_at       = 0;

		$this->event_count_list = [
			Domain_Rating_Entity_Rating::CONVERSATION_MESSAGE => 0,
			Domain_Rating_Entity_Rating::THREAD_MESSAGE       => 0,
			Domain_Rating_Entity_Rating::REACTION             => 0,
			Domain_Rating_Entity_Rating::FILE                 => 0,
			Domain_Rating_Entity_Rating::CALL                 => 0,
			Domain_Rating_Entity_Rating::VOICE                => 0,
			Domain_Rating_Entity_Rating::RESPECT              => 0,
			Domain_Rating_Entity_Rating::EXACTINGNESS         => 0,
		];
	}
}