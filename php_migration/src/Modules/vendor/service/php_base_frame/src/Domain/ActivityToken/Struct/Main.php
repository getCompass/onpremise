<?php

namespace BaseFrame\Domain\ActivityToken\Struct;

/**
 * Основная структура токена
 */
class Main implements \JsonSerializable {

	public function __construct(
		public Header  $header,
		public Payload $payload,
		public string  $signature,
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
			"header"    => $this->header,
			"payload"   => $this->payload,
			"signature" => $this->signature,
		];
	}

}