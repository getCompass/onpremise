<?php declare(strict_types = 1);

namespace Compass\Announcement;

/**
 * Отвязывает пользователя от компании
 */
class Domain_User_Action_UnbindUserToCompany {

	/**
	 * Выполнить действие
	 *
	 * @param int $user_id
	 * @param int $company_id
	 *
	 * @return void
	 */
	public static function do(int $user_id, int $company_id):void {

		Gateway_Db_AnnouncementCompany_CompanyUser::delete($company_id, $user_id);
		Gateway_Db_AnnouncementUser_UserCompany::delete($user_id, $company_id);
	}
}
