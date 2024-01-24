<?php

namespace Compass\Company;

/**
 * Класс-интерфейс для базы данных company_system
 */
class Gateway_Db_CompanySystem_Main extends Gateway_Db_Db {

	protected const _DB_KEY = "company_system";

	// получаем шард ключ для базы данных
	protected static function _getDbKey():string {

		return self::_DB_KEY;
	}
}