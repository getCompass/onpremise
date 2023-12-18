<?php

namespace Compass\Pivot;

/**
 * Структура записи таблицы «last_registered_user».
 */
class Struct_Db_PivotData_LastRegisteredUser {

	public int   $user_id;
	public int   $partner_id;
	public int   $created_at;
	public int   $updated_at;
	public array $extra;

	/**
	 * Constructor.
	 */
	public function __construct(int $user_id, int $partner_id, int $created_at, int $updated_at, array $extra) {

		$this->user_id    = $user_id;
		$this->partner_id = $partner_id;
		$this->created_at = $created_at;
		$this->updated_at = $updated_at;
		$this->extra      = $extra;
	}
}