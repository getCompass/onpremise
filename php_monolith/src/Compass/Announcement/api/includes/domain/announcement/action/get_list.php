<?php declare(strict_types = 1);

namespace Compass\Announcement;

/**
 * Получаем все анонсы
 */
class Domain_Announcement_Action_GetList {

	/**
	 * Выполнить действие
	 *
	 * @param int $user_id
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return Struct_Db_AnnouncementMain_Announcement[]
	 */
	public static function do(int $user_id, int $limit, int $offset):array {

		$company_list           = Gateway_Db_AnnouncementUser_UserCompany::getAllCompanyIdByUserId($user_id);
		$read_announcement_list = Gateway_Db_AnnouncementUser_UserAnnouncement::getAllAnnouncementIdListByUserId($user_id);

		$allowed_status_list = Domain_Announcement_Entity::getActiveStatuses();
		return Gateway_Db_AnnouncementMain_Announcement::getBelongsToUserList($user_id, $company_list, $allowed_status_list, $read_announcement_list, $limit, $offset);
	}
}
