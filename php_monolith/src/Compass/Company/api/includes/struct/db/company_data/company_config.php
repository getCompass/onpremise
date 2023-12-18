<?php

namespace Compass\Company;

/**
 * Класс-структура для таблицы company_data.company_config
 */
class Struct_Db_CompanyData_CompanyConfig {

	/**
	 * Struct_Db_CompanyData_CompanyConfig constructor.
	 *
	 * @param string $key
	 * @param int    $created_at
	 * @param int    $updated_at
	 * @param array  $value
	 */
	public function __construct(
		public string $key,
		public int    $created_at,
		public int    $updated_at,
		public array  $value
	) {

	}

	/**
	 * конвертируем в массив
	 */
	public function convertToArray():array {

		return [
			"key"        => (string) $this->key,
			"created_at" => (int) $this->created_at,
			"updated_at" => (int) $this->updated_at,
			"value"      => (array) $this->value,
		];
	}
}
