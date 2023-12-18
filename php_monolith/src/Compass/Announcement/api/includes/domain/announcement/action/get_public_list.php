<?php declare(strict_types = 1);

namespace Compass\Announcement;

/**
 * Получаем публичные глобальные анонсы
 */
class Domain_Announcement_Action_GetPublicList {

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

		$active_status_list = Domain_Announcement_Entity::getActiveStatuses();
		$blocking_type_list = Domain_Announcement_Entity::getBlockingTypes();

		return Gateway_Db_AnnouncementMain_Announcement::getBelongsToUserPublicList($user_id, $blocking_type_list, $active_status_list, $limit, $offset);
	}

}
