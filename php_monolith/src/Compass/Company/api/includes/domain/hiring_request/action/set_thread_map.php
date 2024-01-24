<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс для действия установки мапы треда у заявки найма
 */
class Domain_HiringRequest_Action_SetThreadMap {

	/**
	 * Выполняем action
	 *
	 * @throws cs_RowNotUpdated
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(int $hiring_request_id, string $thread_map):Struct_Db_CompanyData_HiringRequest {

		Gateway_Db_CompanyData_Main::beginTransaction();

		// получаем запись для обновления
		try {
			$hiring_request = Domain_HiringRequest_Entity_Request::getForUpdate($hiring_request_id);
		} catch (cs_HireRequestNotExist) {

			Gateway_Db_CompanyData_Main::rollback();
			throw new ReturnFatalException("not found hiring request's row");
		}

		// закрепляем мапу треда за заявкой
		$hiring_request->extra = Domain_HiringRequest_Entity_Request::setThreadMap($hiring_request->extra, $thread_map);

		// обновляем данные и коммитим изменения
		$set = ["extra" => $hiring_request->extra, "updated_at" => time()];
		Gateway_Db_CompanyData_HiringRequest::set($hiring_request->hiring_request_id, $set);

		Gateway_Db_CompanyData_Main::commitTransaction();

		return $hiring_request;
	}
}