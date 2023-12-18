<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Структура для вовлеченности пользователя
 */
class Struct_Domain_Usercard_Loyalty {

	public int    $loyalty_id;
	public int    $user_id;
	public int    $creator_user_id;
	public int    $is_deleted;
	public int    $created_at;
	public int    $updated_at;
	public string $comment_text;
	public array  $data;

	/**
	 * Struct_Domain_Usercard_Loyalty constructor.
	 *
	 * @param int    $loyalty_id
	 * @param int    $user_id
	 * @param int    $creator_user_id
	 * @param int    $is_deleted
	 * @param int    $created_at
	 * @param int    $updated_at
	 * @param string $comment_text
	 * @param array  $data
	 */
	public function __construct(int    $loyalty_id, int $user_id, int $creator_user_id, int $is_deleted,
					    int    $created_at, int $updated_at,
					    string $comment_text, array $data) {

		$this->loyalty_id      = $loyalty_id;
		$this->user_id         = $user_id;
		$this->creator_user_id = $creator_user_id;
		$this->is_deleted      = $is_deleted;
		$this->created_at      = $created_at;
		$this->updated_at      = $updated_at;
		$this->comment_text    = $comment_text;
		$this->data            = $data;
	}
}