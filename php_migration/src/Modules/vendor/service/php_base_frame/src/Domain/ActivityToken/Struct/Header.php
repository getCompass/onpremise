<?php

namespace BaseFrame\Domain\ActivityToken\Struct;

/**
 * Структура header в токене
 */
class Header implements \JsonSerializable {

	public function __construct(
		public string $algorithm,
		public string $type,
	) {
	}

	/**
	 * Specify data which should be serialized to JSON
	 * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4
	 */
	public function jsonSerialize():array {

		return [
			"algorithm" => $this->algorithm,
			"type"      => $this->type,
		];
	}
}