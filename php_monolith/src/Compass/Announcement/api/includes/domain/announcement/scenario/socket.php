<?php declare(strict_types = 1);

namespace Compass\Announcement;

/**
 * Сценарии анонсов для API
 */
class Domain_Announcement_Scenario_Socket {

	/**
	 * Добавляет анонс
	 *
	 * @param array  $raw_announcement
	 * @param string $source
	 *
	 * @return Struct_Db_AnnouncementMain_Announcement
	 *
	 * @throws \paramException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function publish(array $raw_announcement, string $source):Struct_Db_AnnouncementMain_Announcement {

		try {
			$announcement = \Service\AnnouncementTemplate\TemplateService::createAnnouncementFromTemplate($raw_announcement);
		} catch (\InvalidArgumentException) {
			throw new \paramException("passed announcement has incorrect format");
		}

		if ($source === "company" && $announcement["is_global"]) {
			throw new \paramException("global announcement can not be published with company_key");
		}

		// создаем структуру для вставки
		$announcement = new Struct_Db_AnnouncementMain_Announcement(
			0,
			$announcement["is_global"],
			$announcement["type"],
			$announcement["status"],
			$announcement["company_id"],
			$announcement["priority"],
			time(),
			time(),
			$announcement["expires_at"],
			$announcement["resend_repeat_time"],
			$announcement["receiver_user_id_list"],
			$announcement["excluded_user_id_list"],
			$announcement["extra"],
		);

		return Domain_Announcement_Action_Publish::do($announcement);
	}

	/**
	 * Удаляет анонс.
	 *
	 * @param int   $company_id
	 * @param int   $type
	 * @param array $extra_filter
	 */
	public static function disable(int $company_id, int $type, array $extra_filter):void {

		Domain_Announcement_Action_Disable::do($type, $company_id, $extra_filter);
	}

	/**
	 * Получить существующие анонсы
	 *
	 * @param int   $company_id
	 * @param array $type_list
	 *
	 * @return array
	 */
	public static function getExistingTypeList(int $company_id, array $type_list):array {

		$announcement_list = Domain_Announcement_Action_GetListByType::do($company_id, $type_list);

		return array_intersect(array_column($announcement_list, "type"), $type_list);
	}
}
