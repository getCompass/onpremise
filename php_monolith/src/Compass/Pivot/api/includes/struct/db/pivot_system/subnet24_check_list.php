<?php

namespace Compass\Pivot;

/**
 * DTO объект
 * сгенерирован автоматически
 * Type_CodeGen_DbStruct::do("pivot_system", "subnet_24_check_list");
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Db_PivotSystem_Subnet24CheckList {

	// конструктор
	protected function __construct(
		public int   $subnet_24,
		public int   $status = 0,
		public int   $checked_ip = 0,
		public int   $need_work = 0,
		public int   $created_at = 0,
		public int   $updated_at = 0,
		public array $extra = [],
	) {

	}

	// формируем объект из массива
	public static function rowToStruct(array $arr):self {

		return new self(
			$arr["subnet_24"],
			$arr["status"],
			$arr["checked_ip"],
			$arr["need_work"],
			$arr["created_at"],
			$arr["updated_at"],
			fromJson($arr["extra"]),
		);
	}

	// пустой объект
	public static function empty(int $subnet_24):self {

		return new self($subnet_24);
	}

}