<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс для действия установки мапы треда у заявки увольнения
 */
class Domain_DismissalRequest_Action_SetThreadMap {

	/**
	 * Выполняем action
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(int $dismissal_request_id, string $thread_map):Struct_Db_CompanyData_DismissalRequest {

		Gateway_Db_CompanyData_Main::beginTransaction();

		// получаем запись для обновления
		try {
			$dismissal_request = Domain_DismissalRequest_Entity_Request::getForUpdate($dismissal_request_id);
		} catch (cs_HireRequestNotExist) {

			Gateway_Db_CompanyData_Main::rollback();
			throw new ReturnFatalException("not found dismissal request's row");
		}

		// закрепляем мапу треда за заявкой
		$dismissal_request->extra = Domain_DismissalRequest_Entity_Request::setThreadMap($dismissal_request->extra, $thread_map);

		// обновляем данные и коммитим изменения
		$set = ["extra" => $dismissal_request->extra, "updated_at" => time()];
		Gateway_Db_CompanyData_DismissalRequest::set($dismissal_request->dismissal_request_id, $set);

		Gateway_Db_CompanyData_Main::commitTransaction();

		return $dismissal_request;
	}
}