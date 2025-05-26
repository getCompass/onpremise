<?php

namespace Compass\Pivot;

/**
 * DTO объект
 * сгенерирован автоматически
 * Type_CodeGen_DbStruct::do("pivot_system", "subnet_24_result_list");
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Db_PivotSystem_Subnet24ResultList {

	// конструктор
	protected function __construct(
		public int    $subnet_24,
		public int    $is_mobile = 0,
		public int    $is_proxy = 0,
		public int    $is_hosting = 0,
		public string $country_code = "",
		public string $as = "",
		public int    $created_at = 0,
		public int    $updated_at = 0,
	) {

	}

	// формируем объект из массива
	public static function rowToStruct(array $arr):self {

		return new self(
			$arr["subnet_24"],
			$arr["is_mobile"],
			$arr["is_proxy"],
			$arr["is_hosting"],
			$arr["country_code"],
			$arr["as"],
			$arr["created_at"],
			$arr["updated_at"],
		);
	}

	// пустой объект
	public static function empty(int $subnet_24):self {

		return new self($subnet_24);
	}

}