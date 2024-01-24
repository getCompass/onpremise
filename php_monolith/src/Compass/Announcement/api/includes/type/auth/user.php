<?php

namespace Compass\Announcement;

/**
 * Основной класс для работы с пользователем
 */
class Type_Auth_User {

	/**
	 * @var int
	 */
	private int $user_id;

	/**
	 * @param int $user_id
	 */
	public function __construct(int $user_id = 0) {

		$this->user_id = $user_id;
	}

	/**
	 * Получить id пользователя
	 *
	 * @return int
	 */
	public function getUserId():int {

		return $this->user_id;
	}
}