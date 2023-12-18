<?php

namespace Compass\Company;

/**
 * Action для удаления всех активных сессий пользователя
 */
class Domain_User_Action_UserSessionActive_DeleteAllByUser {

	/**
	 *
	 * @throws \busException
	 */
	public static function do(int $user_id):void {

		Gateway_Db_CompanyData_SessionActiveList::deleteByUser($user_id);

		Gateway_Bus_CompanyCache::clearSessionCacheByUserId($user_id);
	}
}
