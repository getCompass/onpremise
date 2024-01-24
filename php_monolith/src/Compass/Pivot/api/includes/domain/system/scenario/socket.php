<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\EndpointAccessDeniedException;
use BaseFrame\Server\ServerProvider;

/**
 * Системные сценарии
 */
class Domain_System_Scenario_Socket {

	/**
	 * Получить id коммпаний на домино
	 *
	 */
	public static function getDominoCompanyIdList(string $domino_id, int $limit = 99999999, int $offset = 0):array {

		return Gateway_Db_PivotCompanyService_CompanyRegistry::getAllCompanyIdList($domino_id, $limit, $offset);
	}

	/**
	 * Получить id активных коммпаний
	 *
	 */
	public static function getActiveDominoCompanyIdList(string $domino_id):array {

		return Gateway_Db_PivotCompanyService_CompanyRegistry::getActiveCompanyIdList($domino_id);
	}

	/**
	 * Чистим весь кэш
	 */
	public static function clearAllCache():void {

		ShardingGateway::cache()->flush();
	}

	/**
	 * Получить user_id рут пользователя он-премайз.
	 */
	public static function getRootUserId():int {

		if (!ServerProvider::isOnPremise()) {
			throw new EndpointAccessDeniedException("only for on-premise server");
		}

		return Domain_User_Entity_OnpremiseRoot::getUserId();
	}
}
