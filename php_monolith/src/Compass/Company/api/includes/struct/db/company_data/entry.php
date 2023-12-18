<?php

namespace Compass\Company;

/**
 * Класс-структура для таблицы company_data.entry_list
 */
class Struct_Db_CompanyData_Entry {

	public int $entry_id;
	public int $entry_type;
	public int $user_id;
	public int $created_at;

	/**
	 * Struct_Db_CompanyData_Entry constructor.
	 */
	public function __construct(int $entry_id, int $entry_type, int $user_id, int $created_at) {

		$this->entry_id   = $entry_id;
		$this->entry_type = $entry_type;
		$this->user_id    = $user_id;
		$this->created_at = $created_at;
	}
}