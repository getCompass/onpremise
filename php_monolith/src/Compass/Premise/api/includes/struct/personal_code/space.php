<?php declare(strict_types = 1);

namespace Compass\Premise;

/**
 * Объект команды для персонального кода
 */
class Struct_PersonalCode_Space {

	public string $masked_name;

	public function __construct(
		public string $name,
		public int    $member_count,
		public string $role,
		public array  $permission_list
	) {

		$this->masked_name = obfuscateWords($name, 1);
	}

	/**
	 * Перевести в массив
	 */
	public function toArray():array {

		return [
			"masked_name"     => $this->masked_name,
			"member_count"    => $this->member_count,
			"role"            => $this->role,
			"permission_list" => $this->permission_list,
		];
	}
}