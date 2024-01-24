<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_phone.phone_uniq_list_{0-f}
 */
class Struct_Db_PivotPhone_PhoneUniq {

	public string $phone_number_hash;
	public int    $user_id;
	public int    $binding_count;
	public int    $last_binding_at;
	public int    $last_unbinding_at;
	public int    $created_at;
	public int    $updated_at;
	public array  $previous_user_list;

	/**
	 * Struct_Db_PivotPhone_PhoneUniq constructor.
	 *
	 */
	public function __construct(string $phone_number_hash, int $user_id, int $binding_count, int $last_binding_at, int $last_unbinding_at, int $created_at, int $updated_at, array $previous_user_list) {

		$this->phone_number_hash  = $phone_number_hash;
		$this->user_id            = $user_id;
		$this->binding_count      = $binding_count;
		$this->last_binding_at    = $last_binding_at;
		$this->last_unbinding_at  = $last_unbinding_at;
		$this->created_at         = $created_at;
		$this->updated_at         = $updated_at;
		$this->previous_user_list = $previous_user_list;
	}
}