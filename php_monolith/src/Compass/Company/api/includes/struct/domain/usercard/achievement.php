<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Структура для достижения пользователя
 */
class Struct_Domain_Usercard_Achievement {

	public int    $achievement_id;
	public int    $user_id;
	public int    $creator_user_id;
	public int    $type;
	public int    $is_deleted;
	public int    $created_at;
	public int    $updated_at;
	public string $header_text;
	public string $description_text;
	public array  $data;

	/**
	 * Struct_Domain_Usercard_Achievement constructor.
	 *
	 * @param int    $achievement_id
	 * @param int    $user_id
	 * @param int    $creator_user_id
	 * @param int    $type
	 * @param int    $is_deleted
	 * @param int    $created_at
	 * @param int    $updated_at
	 * @param string $header_text
	 * @param string $description_text
	 * @param array  $data
	 */
	public function __construct(int    $achievement_id, int $user_id, int $creator_user_id, int $type, int $is_deleted,
					    int    $created_at, int $updated_at,
					    string $header_text, string $description_text,
					    array  $data) {

		$this->achievement_id   = $achievement_id;
		$this->user_id          = $user_id;
		$this->creator_user_id  = $creator_user_id;
		$this->type             = $type;
		$this->is_deleted       = $is_deleted;
		$this->created_at       = $created_at;
		$this->updated_at       = $updated_at;
		$this->header_text      = $header_text;
		$this->description_text = $description_text;
		$this->data             = $data;
	}
}