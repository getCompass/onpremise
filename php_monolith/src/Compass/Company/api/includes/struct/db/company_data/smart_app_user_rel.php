<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Класс-структура для таблицы company_data.smart_app_user_rel
 */
class Struct_Db_CompanyData_SmartAppUserRel {

	/**
	 * Struct_Db_CompanyData_SmartAppUserRel constructor.
	 */
	public function __construct(
		public int   $smart_app_id,
		public int   $user_id,
		public int   $status,
		public int   $deleted_at,
		public int   $created_at,
		public int   $updated_at,
		public array $extra,
	) {
	}

	// формируем объект из массива
	public static function rowToStruct(array $row):self {

		return new self(
			$row["smart_app_id"],
			$row["user_id"],
			$row["status"],
			$row["deleted_at"],
			$row["created_at"],
			$row["updated_at"],
			fromJson($row["extra"])
		);
	}
}