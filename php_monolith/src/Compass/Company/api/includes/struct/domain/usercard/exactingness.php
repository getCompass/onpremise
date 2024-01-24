<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Структура требовательности
 */
class Struct_Domain_Usercard_Exactingness {

	public int   $exactingness_id;
	public int   $type;
	public int   $user_id;
	public int   $creator_user_id;
	public int   $is_deleted;
	public int   $created_at;
	public int   $updated_at;
	public array $data;

	/**
	 * Struct_Domain_Usercard_Exactingness constructor.
	 *
	 * @param int   $exactingness_id
	 * @param int   $type
	 * @param int   $user_id
	 * @param int   $creator_user_id
	 * @param int   $is_deleted
	 * @param int   $created_at
	 * @param int   $updated_at
	 * @param array $data
	 */
	public function __construct(int   $exactingness_id, int $type, int $user_id, int $creator_user_id, int $is_deleted,
					    int   $created_at, int $updated_at,
					    array $data) {

		$this->exactingness_id = $exactingness_id;
		$this->type            = $type;
		$this->user_id         = $user_id;
		$this->creator_user_id = $creator_user_id;
		$this->is_deleted      = $is_deleted;
		$this->created_at      = $created_at;
		$this->updated_at      = $updated_at;
		$this->data            = $data;
	}
}