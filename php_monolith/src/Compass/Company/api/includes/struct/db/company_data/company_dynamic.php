<?php

namespace Compass\Company;

/**
 * Класс-структура для таблицы company_data.company_dynamic
 */
class Struct_Db_CompanyData_CompanyDynamic {

	public string $key;
	public int    $value;
	public int    $created_at;
	public int    $updated_at;

	/**
	 * Struct_Db_CompanyData_CompanyDynamic constructor.
	 */
	public function __construct(
		string $key,
		int    $value,
		int    $created_at,
		int    $updated_at
	) {

		$this->key        = $key;
		$this->value      = $value;
		$this->created_at = $created_at;
		$this->updated_at = $updated_at;
	}
}
