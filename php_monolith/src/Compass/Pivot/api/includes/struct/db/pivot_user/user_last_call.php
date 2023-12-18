<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_user_{10m}.user_last_call_{1}
 */
class Struct_Db_PivotUser_UserLastCall {

	public int    $user_id;
	public int    $company_id;
	public string $call_key;
	public int    $is_finished;
	public int    $type;
	public int    $created_at;
	public int    $updated_at;
	public array  $extra;

	/**
	 * Struct_Db_PivotCompany_Company constructor.
	 *
	 */
	public function __construct(
		int    $user_id,
		int    $company_id,
		string $call_key,
		int    $is_finished,
		int    $type,
		int    $created_at,
		int    $updated_at,
		array  $extra
	) {

		$this->user_id     = $user_id;
		$this->company_id  = $company_id;
		$this->call_key    = $call_key;
		$this->is_finished = $is_finished;
		$this->type        = $type;
		$this->created_at  = $created_at;
		$this->updated_at  = $updated_at;
		$this->extra       = $extra;
	}
}
