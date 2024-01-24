<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Структура для спринта пользователя
 */
class Struct_Domain_Usercard_Sprint {

	public int    $sprint_id;
	public int    $user_id;
	public int    $is_success;
	public int    $is_deleted;
	public int    $creator_user_id;
	public int    $started_at;
	public int    $end_at;
	public int    $created_at;
	public int    $updated_at;
	public string $header_text;
	public string $description_text;
	public array  $data;

	/**
	 * Struct_Domain_Usercard_Sprint constructor.
	 *
	 * @param int    $sprint_id
	 * @param int    $user_id
	 * @param int    $is_success
	 * @param int    $is_deleted
	 * @param int    $creator_user_id
	 * @param int    $started_at
	 * @param int    $end_at
	 * @param int    $created_at
	 * @param int    $updated_at
	 * @param string $header_text
	 * @param string $description_text
	 * @param array  $data
	 */
	public function __construct(int    $sprint_id, int $user_id, int $is_success, int $is_deleted, int $creator_user_id,
					    int    $started_at, int $end_at,
					    int    $created_at, int $updated_at,
					    string $header_text, string $description_text, array $data) {

		$this->sprint_id        = $sprint_id;
		$this->user_id          = $user_id;
		$this->is_success       = $is_success;
		$this->is_deleted       = $is_deleted;
		$this->creator_user_id  = $creator_user_id;
		$this->started_at       = $started_at;
		$this->end_at           = $end_at;
		$this->created_at       = $created_at;
		$this->updated_at       = $updated_at;
		$this->header_text      = $header_text;
		$this->description_text = $description_text;
		$this->data             = $data;
	}
}