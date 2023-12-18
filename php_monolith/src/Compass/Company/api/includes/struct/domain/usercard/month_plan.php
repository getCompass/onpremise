<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Структура для плана на месяц пользователя
 */
class Struct_Domain_Usercard_MonthPlan {

	public int $row_id;
	public int $user_id;
	public int $type;
	public int $plan_value;
	public int $user_value;
	public int $created_at;
	public int $updated_at;

	/**
	 * Struct_Domain_Usercard_MonthPlan constructor.
	 */
	public function __construct(int $row_id, int $user_id, int $type,
					    int $plan_value, int $user_value,
					    int $created_at, int $updated_at) {

		$this->row_id     = $row_id;
		$this->user_id    = $user_id;
		$this->type       = $type;
		$this->plan_value = $plan_value;
		$this->user_value = $user_value;
		$this->created_at = $created_at;
		$this->updated_at = $updated_at;
	}
}