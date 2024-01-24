<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Структура для рабочих часов пользователя
 */
class Struct_Domain_Usercard_WorkedHours {

	public int   $worked_hour_id;
	public int   $user_id;
	public int   $day_start_at;
	public int   $type;
	public int   $is_deleted;
	public float $float_value;
	public int   $created_at;
	public int   $updated_at;
	public array $data;

	/**
	 * Struct_Domain_Usercard_WorkedHours constructor.
	 */
	public function __construct(int $worked_hour_id, int $user_id, int $day_start_at, int $type,
					    int $is_deleted, float $float_value,
					    int $created_at, int $updated_at, array $data) {

		$this->worked_hour_id = $worked_hour_id;
		$this->user_id        = $user_id;
		$this->day_start_at   = $day_start_at;
		$this->type           = $type;
		$this->is_deleted     = $is_deleted;
		$this->float_value    = $float_value;
		$this->created_at     = $created_at;
		$this->updated_at     = $updated_at;
		$this->data           = $data;
	}
}