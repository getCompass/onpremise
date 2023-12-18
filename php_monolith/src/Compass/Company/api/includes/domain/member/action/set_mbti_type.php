<?php

namespace Compass\Company;

/**
 * Действие обновления mbti
 */
class Domain_Member_Action_SetMbtiType {

	/**
	 *
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	public static function do(int $user_id, string $mbti_type):void {

		// формируем массив на обновление
		$updated = [
			"updated_at" => time(),
			"mbti_type"  => $mbti_type,
		];

		Gateway_Db_CompanyData_MemberList::set($user_id, $updated);
		Gateway_Bus_CompanyCache::clearMemberCacheByUserId($user_id);

		$user_info = Gateway_Db_CompanyData_MemberList::getOne($user_id);

		// отправляем WS
		Gateway_Bus_Sender::memberProfileUpdated($user_info);
	}
}