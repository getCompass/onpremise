<?php declare(strict_types = 1);

namespace Compass\Premise;

/**
 * Объект информации для персонального кода
 */
class Struct_PersonalCode_Data {

	public string $masked_full_name;

	public function __construct(
		public string $full_name,
		public array  $space_list
	) {

		$this->masked_full_name = obfuscateWords($full_name, 1);
	}

	/**
	 * Перевести в массив
	 */
	public function toArray():array {

		return [
			"masked_full_name" => $this->masked_full_name,
			"space_list"       => array_map(fn(Struct_PersonalCode_Space $space) => $space->toArray(), $this->space_list),
		];
	}
}