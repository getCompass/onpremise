<?php

namespace Compass\Company;

/**
 * Действие обновления статуса
 */
class Domain_Member_Action_SetStatus {

	/**
	 * выполняем метод
	 *
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	public static function do(int $user_id, string $status):void {

		// формируем массив на обновление
		$updated = [
			"updated_at" => time(),
			"comment"    => $status,
		];

		Gateway_Db_CompanyData_MemberList::set($user_id, $updated);

		$user_info = Gateway_Db_CompanyData_MemberList::getOne($user_id);

		// отправляем WS
		Gateway_Bus_Sender::memberProfileUpdated($user_info);
	}
}