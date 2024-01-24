<?php

namespace Compass\Speaker;

/**
 * класс-интерфейс для таблицы company_call.call_history
 */
class Gateway_Db_CompanyCall_CallHistory extends Gateway_Db_CompanyCall_Main {

	protected const _TABLE_KEY = "call_history";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// вставляем запись
	public static function insertArray(array $insert):void {

		ShardingGateway::database(self::_getDbKey())->insertArray(self::_TABLE_KEY, $insert);
	}
}