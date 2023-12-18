<?php

namespace Compass\Pivot;

/**
 * Создает порт на доминошке
 */
class Domain_Domino_Action_CreatePort {

	/**
	 * Создает порт на доминошке
	 *
	 * @param string $domino_id
	 * @param int    $port
	 * @param int    $type_int
	 * @param string $encrypted_mysql_user
	 * @param string $encrypted_mysql_pass
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws \busException
	 * @throws \queryException
	 */
	public static function do(string $domino_id, int $port, int $type_int, string $encrypted_mysql_user, string $encrypted_mysql_pass):void {

		$status = Domain_Domino_Entity_Port_Registry::STATUS_VOID;
		$time   = time();

		// формируем extra
		$extra  = Domain_Domino_Entity_Port_Registry::initExtra($encrypted_mysql_user, $encrypted_mysql_pass);
		$domino = new Struct_Db_PivotCompanyService_PortRegistry(
			$port, $status, $type_int, 0, $time, 0, 0, $extra
		);

		// создаем на домино
		$domino_registry = Gateway_Db_PivotCompanyService_DominoRegistry::getOne($domino_id);
		Gateway_Bus_DatabaseController::addPort($domino_registry, $port, $status, $type_int, 0, $time, 0, 0, 0, $extra);

		// создаем в локальной базе
		Gateway_Db_PivotCompanyService_PortRegistry::insert($domino_id, $domino);

		$set = [];

		switch ($type_int) {

			case Domain_Domino_Entity_Port_Registry::TYPE_SERVICE:

				$set["service_port_count"] = "service_port_count + 1";
				break;
			case Domain_Domino_Entity_Port_Registry::TYPE_COMMON:

				$set["common_port_count"] = "common_port_count + 1";
				break;
			case Domain_Domino_Entity_Port_Registry::TYPE_RESERVE:
				$set["reserved_port_count"] = "reserved_port_count + 1";
		}

		Gateway_Db_PivotCompanyService_DominoRegistry::set($domino_id, $set);
	}
}
