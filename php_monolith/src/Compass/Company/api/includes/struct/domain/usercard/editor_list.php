<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Структура для списка редакторов пользователя
 */
class Struct_Domain_Usercard_EditorList {

	public int   $user_id;
	public array $editor_list;

	/**
	 * Struct_Domain_Usercard_EditorList constructor.
	 */
	public function __construct(int $user_id, array $editor_list) {

		$this->user_id     = $user_id;
		$this->editor_list = $editor_list;
	}
}