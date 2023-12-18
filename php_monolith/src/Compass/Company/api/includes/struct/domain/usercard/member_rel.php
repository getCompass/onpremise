<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Структура для записи соотношения роли между пользователями
 */
class Struct_Domain_Usercard_MemberRel {

	public int $row_id;
	public int $user_id;
	public int $role;
	public int $recipient_user_id;
	public int $is_deleted;
	public int $created_at;
	public int $updated_at;

	/**
	 * Struct_Domain_Usercard_MemberRel constructor.
	 */
	public function __construct(int $row_id, int $user_id, int $role, int $recipient_user_id, int $is_deleted, int $created_at, int $updated_at) {

		$this->row_id            = $row_id;
		$this->user_id           = $user_id;
		$this->role              = $role;
		$this->recipient_user_id = $recipient_user_id;
		$this->is_deleted        = $is_deleted;
		$this->created_at        = $created_at;
		$this->updated_at        = $updated_at;
	}
}