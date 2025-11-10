<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\DBShardingNotFoundException;
use BaseFrame\Exception\Gateway\QueryFatalException;

/**
 * Класс action для удаления приложения
 */
class Domain_SmartApp_Action_Delete {

	/**
	 * выполняем действие
	 *
	 * @param Struct_Db_CompanyData_SmartAppList    $smart_app
	 * @param Struct_Db_CompanyData_SmartAppUserRel $smart_app_user_rel
	 *
	 * @return int
	 * @throws DBShardingNotFoundException
	 * @throws ParseFatalException
	 * @throws QueryFatalException
	 */
	public static function do(Struct_Db_CompanyData_SmartAppList $smart_app, Struct_Db_CompanyData_SmartAppUserRel $smart_app_user_rel):int {

		// если уже удалён
		if ($smart_app_user_rel->status === Domain_SmartApp_Entity_SmartAppUserRel::STATUS_DELETE) {
			return $smart_app_user_rel->deleted_at;
		}

		// помечаем приложение удалённым
		$deleted_at                     = time();
		$smart_app->smart_app_uniq_name = "";
		Domain_SmartApp_Entity_SmartApp::delete($smart_app);

		$smart_app_user_rel->deleted_at = $deleted_at;
		Domain_SmartApp_Entity_SmartAppUserRel::delete($smart_app_user_rel);

		// отправляем ws-событие о удалении приложения пользователю
		Gateway_Bus_Sender::smartAppDeleted($smart_app_user_rel->smart_app_id, $smart_app_user_rel->user_id);

		return $deleted_at;
	}
}