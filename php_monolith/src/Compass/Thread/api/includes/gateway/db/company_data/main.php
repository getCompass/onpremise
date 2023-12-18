<?php

namespace Compass\Thread;

/**
 * класс-интерфейс для базы данных company_data
 */
class Gateway_Db_CompanyData_Main extends Gateway_Db_Db {

	protected const _DB_KEY = "company_data";

	// получаем шард ключ для базы данных
	protected static function _getDbKey():string {

		return self::_DB_KEY;
	}
}