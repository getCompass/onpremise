<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Структура для респекта пользователя
 */
class Struct_Domain_Usercard_Respect {

	public int    $respect_id;
	public int    $type;
	public int    $user_id;
	public int    $is_deleted;
	public int    $creator_user_id;
	public int    $created_at;
	public int    $updated_at;
	public string $respect_text;
	public array  $data;

	/**
	 * Struct_Domain_Usercard_Respect constructor.
	 *
	 * @param int    $respect_id
	 * @param int    $type
	 * @param int    $user_id
	 * @param int    $creator_user_id
	 * @param int    $is_deleted
	 * @param int    $created_at
	 * @param int    $updated_at
	 * @param string $respect_text
	 * @param array  $data
	 */
	public function __construct(int    $respect_id, int $type, int $user_id, int $creator_user_id, int $is_deleted,
					    int    $created_at, int $updated_at,
					    string $respect_text, array $data) {

		$this->respect_id      = $respect_id;
		$this->type            = $type;
		$this->user_id         = $user_id;
		$this->is_deleted      = $is_deleted;
		$this->creator_user_id = $creator_user_id;
		$this->created_at      = $created_at;
		$this->updated_at      = $updated_at;
		$this->respect_text    = $respect_text;
		$this->data            = $data;
	}
}