<?php

namespace Compass\Jitsi;

/** структура api-сущности описывающей аватар создателя конференции */
class Struct_Api_Conference_CreatorData_Avatar {

	public function __construct(
		public string $file_key,
		public string $color,
	) {
	}

	/** форматируем сущность для ответа */
	public function format():array {

		return [
			"file_key" => (string) $this->file_key,
			"color"    => (string) $this->color,
		];
	}
}
