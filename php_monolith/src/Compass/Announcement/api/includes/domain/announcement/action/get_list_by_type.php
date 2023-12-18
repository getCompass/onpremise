<?php declare(strict_types = 1);

namespace Compass\Announcement;

/**
 * Получаем все анонсы
 */
class Domain_Announcement_Action_GetListByType {

	/**
	 * Выполнить действие
	 *
	 * @param int   $company_id
	 * @param array $type_list
	 *
	 * @return Struct_Db_AnnouncementMain_Announcement[]
	 */
	public static function do(int $company_id, array $type_list):array {

		$allowed_status_list = Domain_Announcement_Entity::getActiveStatuses();
		return Gateway_Db_AnnouncementMain_Announcement::getActiveListByType($company_id, $allowed_status_list,$type_list, count($type_list));
	}
}
