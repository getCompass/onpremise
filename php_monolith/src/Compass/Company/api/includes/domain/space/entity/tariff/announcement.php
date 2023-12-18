<?php

namespace Compass\Company;

/**
 * Анонсы, связанные с тарифными планами
 */
class Domain_Space_Entity_Tariff_Announcement {

	// анонсы, которые разрешено постить с помощью данного экшна
	protected const _ALLOWED_ANNOUNCEMENT_TYPE_LIST = [
		\Service\AnnouncementTemplate\AnnouncementType::SPACE_TARIFF_EXPIRATION,
		\Service\AnnouncementTemplate\AnnouncementType::SPACE_TARIFF_EXPIRED,
	];

	// анонсы, которые необходимо отключить при публикации текущего
	protected const _NEED_DISABLE_ANNOUNCEMENT_TYPE_LIST = [
		\Service\AnnouncementTemplate\AnnouncementType::SPACE_TARIFF_EXPIRATION => [],
		\Service\AnnouncementTemplate\AnnouncementType::SPACE_TARIFF_EXPIRED    => [
			\Service\AnnouncementTemplate\AnnouncementType::SPACE_TARIFF_EXPIRATION,
		],
	];

	/**
	 * Публикуем анонс
	 *
	 * @param int   $announcement_type
	 * @param array $data
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function publish(int $announcement_type, array $data):void {

		// если кто то решил воспользоваться публикацией анонсов для тарифов для публикации других анонсов - получит ошибку
		if (!in_array($announcement_type, self::_ALLOWED_ANNOUNCEMENT_TYPE_LIST, true)) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("not allowed announcement_type");
		}

		// отрубаем анонсы, которые несовместимы с текущим
		foreach (self::_NEED_DISABLE_ANNOUNCEMENT_TYPE_LIST[$announcement_type] as $disable_announcement_type) {
			Domain_Announcement_Action_Disable::run($disable_announcement_type);
		}

		// получаем все администраторов
		$receiver_member_list = Domain_User_Action_Member_GetUserRoleList::do([\CompassApp\Domain\Member\Entity\Member::ROLE_ADMINISTRATOR]);

		// публикуем анонс
		$raw_data = \Service\AnnouncementTemplate\TemplateService::createOfType(
			$announcement_type, $data);

		Domain_Announcement_Action_Publish::run($raw_data, COMPANY_ID, array_column($receiver_member_list, "user_id"));
	}

	/**
	 * Выключаем анонсы
	 *
	 * @return void
	 */
	public static function disable():void {

		foreach (self::_ALLOWED_ANNOUNCEMENT_TYPE_LIST as $announcement_type) {
			Domain_Announcement_Action_Disable::run($announcement_type);
		}
	}
}