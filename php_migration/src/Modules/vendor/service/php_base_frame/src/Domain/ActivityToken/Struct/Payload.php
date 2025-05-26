<?php

namespace BaseFrame\Domain\ActivityToken\Struct;

/**
 * Структура payload в токене
 */
class Payload implements \JsonSerializable {

	public function __construct(
		public string $token_uniq,
		public int    $user_id,
		public int    $expires_at,
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
			"token_uniq" => $this->token_uniq,
			"user_id"    => $this->user_id,
			"expires_at" => $this->expires_at,
		];
	}
}