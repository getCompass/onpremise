<?php

namespace Compass\Pivot;

/**
 * Изменяет количество портов указанного типа на домино.
 */
class Domain_Domino_Action_DoActivePortCountDelta {

	/**
	 * Изменяет количество портов указанного типа на домино.
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function doPortCountDelta(int $delta, string $domino_id, int $port_type):void {

		$set = [
			"updated_at" => time(),
		];

		switch ($port_type) {

			case Domain_Domino_Entity_Port_Registry::TYPE_SERVICE:

				$set["service_active_port_count"] = "service_active_port_count + {$delta}";
				break;
			case Domain_Domino_Entity_Port_Registry::TYPE_COMMON:

				$set["common_active_port_count"] = "common_active_port_count + {$delta}";
				break;
			case Domain_Domino_Entity_Port_Registry::TYPE_RESERVE:

				$set["reserve_active_port_count"] = "reserve_active_port_count + {$delta}";
				break;
			default:
				throw new \BaseFrame\Exception\Domain\ParseFatalException("passed unknown port type {$port_type}");
		}

		Gateway_Db_PivotCompanyService_DominoRegistry::set($domino_id, $set);
	}
}