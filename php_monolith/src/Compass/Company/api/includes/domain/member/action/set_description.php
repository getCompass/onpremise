<?php

namespace Compass\Company;

/**
 * Действие обновления описания
 */
class Domain_Member_Action_SetDescription {

	/**
	 *
	 *
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	public static function do(int $user_id, string $description):void {

		// формируем массив на обновление
		$updated = [
			"updated_at"        => time(),
			"short_description" => $description,
		];

		Gateway_Db_CompanyData_MemberList::set($user_id, $updated);
		Gateway_Bus_CompanyCache::clearMemberCacheByUserId($user_id);

		$user_info = Gateway_Db_CompanyData_MemberList::getOne($user_id);

		// отправляем WS
		Gateway_Bus_Sender::memberProfileUpdated($user_info);

		// обновляем данные в intercom
		Gateway_Socket_Intercom::setMember($user_id, description: $description);
	}
}