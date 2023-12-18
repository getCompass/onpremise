<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Структура для dynamic-данных карточки пользователя
 */
class Struct_Domain_Usercard_Dynamic {

	public int   $user_id;
	public int   $created_at;
	public int   $updated_at;
	public array $data;

	/**
	 * Struct_Domain_Usercard_Dynamic constructor.
	 */
	public function __construct(int $user_id, int $created_at, int $updated_at, array $data) {

		$this->user_id    = $user_id;
		$this->created_at = $created_at;
		$this->updated_at = $updated_at;
		$this->data       = $data;
	}
}