<?php

namespace Compass\Conversation;

/**
 * класс-структура для пользователей чата
 */
class Struct_Conversation_User {

	/**
	 * @param int $user_id
	 * @param int $role
	 */
	public function __construct(
		public int $user_id,
		public int $role,
	) {

	}

	/**
	 * Превратить в массив
	 *
	 * @return array
	 */
	public function toArray():array {

		return [
			"user_id" => $this->user_id,
			"role"    => $this->role,
		];
	}

	/**
	 * Достать из массива
	 *
	 * @param array $data
	 *
	 * @return Struct_Conversation_User
	 */
	public static function fromArray(array $data):self {

		return new self(
			$data["user_id"],
			$data["role"]
		);
	}
}