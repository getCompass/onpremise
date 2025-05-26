<?php

namespace AnalyticUtils\Domain\Counter\Struct;

/**
 * Класс-структура для пользовательских счетчиков
 */
class User implements \JsonSerializable {

	public function __construct(
		public int    $user_id,
		public string $action,
		public string $row,
		public int    $value,
	) {
	}

	/**
	 * Сериализируем в JSON
	 *
	 * @return array
	 */
	public function jsonSerialize():array {

		return [
			"user_id" => $this->user_id,
			"action"  => $this->action,
			"row"     => $this->row,
			"value"   => $this->value,
		];
	}
}