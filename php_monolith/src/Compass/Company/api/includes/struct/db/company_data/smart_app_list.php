<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Класс-структура для таблицы company_data.smart_app_list
 */
class Struct_Db_CompanyData_SmartAppList {

	/**
	 * Struct_Db_CompanyData_SmartAppList constructor.
	 */
	public function __construct(
		public int    $smart_app_id,
		public int    $catalog_item_id,
		public int    $creator_user_id,
		public int    $created_at,
		public int    $updated_at,
		public string $smart_app_uniq_name,
		public array  $extra,
	) {
	}

	// формируем объект из массива
	public static function rowToStruct(array $row):self {

		return new self(
			$row["smart_app_id"],
			$row["catalog_item_id"],
			$row["creator_user_id"],
			$row["created_at"],
			$row["updated_at"],
			$row["smart_app_uniq_name"],
			fromJson($row["extra"])
		);
	}
}