<?php declare(strict_types = 1);

namespace Compass\Announcement;

/**
 * Привязывает пользователя к компании
 */
class Domain_User_Action_BindUserToCompany {

	/**
	 * Выполнить действие
	 *
	 * @param int $user_id
	 * @param int $company_id
	 * @param int $expires_at
	 *
	 * @return void
	 */
	public static function do(int $user_id, int $company_id, int $expires_at):void {

		Gateway_Db_AnnouncementCompany_CompanyUser::insertOrUpdate($company_id, $user_id, $expires_at);
		Gateway_Db_AnnouncementUser_UserCompany::insertOrUpdate($user_id, $company_id, $expires_at);
	}
}
