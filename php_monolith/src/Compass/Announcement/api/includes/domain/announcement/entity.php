<?php

namespace Compass\Announcement;

use Service\AnnouncementTemplate\AnnouncementType;
use Service\AnnouncementTemplate\AnnouncementStatus;

/**
 * Константные данные для сущности анонсов.
 */
class Domain_Announcement_Entity {

	/** @var array анонсы с блокирующим флагом */
	protected const _BLOCKING_TYPE_LIST = [
		AnnouncementType::APPLICATION_UNAVAILABLE,
		AnnouncementType::APPLICATION_TECHNICAL_WORKS_IN_PROGRESS,
		AnnouncementType::APP_VERSION_OUTDATED_IOS,
		AnnouncementType::APP_VERSION_OUTDATED_ANDROID,
		AnnouncementType::APP_VERSION_OUTDATED_ELECTRON,
		AnnouncementType::TEST_GLOBAL_BLOCKING,
		AnnouncementType::TEST_COMPANY_BLOCKING,
		AnnouncementType::COMPANY_IS_IN_HIBERNATION_MODE,
		AnnouncementType::COMPANY_TECHNICAL_WORKS_IN_PROGRESS,
		AnnouncementType::COMPANY_IS_PURGING,
		AnnouncementType::COMPANY_IS_MIGRATING,
	];

	/** @var array анонсы с флагом уникальности — обновляют текущий активный, а не создают новый */
	protected const _UNIQ_TYPE_LIST = [
		AnnouncementType::TEST_GLOBAL_BLOCKING,
		AnnouncementType::APPLICATION_UNAVAILABLE,
		AnnouncementType::APPLICATION_TECHNICAL_WORKS_IN_PROGRESS,
		AnnouncementType::APP_VERSION_OUTDATED_IOS,
		AnnouncementType::APP_VERSION_OUTDATED_ANDROID,
		AnnouncementType::APP_VERSION_OUTDATED_ELECTRON,
		AnnouncementType::APP_VERSION_AVAILABLE_IOS,
		AnnouncementType::APP_VERSION_AVAILABLE_ANDROID,
		AnnouncementType::APP_VERSION_AVAILABLE_ELECTRON,
		AnnouncementType::TEST_COMPANY_BLOCKING,
		AnnouncementType::COMPANY_IS_IN_HIBERNATION_MODE,
		AnnouncementType::COMPANY_TECHNICAL_WORKS_IN_PROGRESS,
		AnnouncementType::COMPANY_IS_PURGING,
		AnnouncementType::COMPANY_WAS_PURGED,
		AnnouncementType::COMPANY_WAS_DELETED,
		AnnouncementType::COMPANY_TECHNICAL_WORKS_NOTICE,
		AnnouncementType::SPACE_TARIFF_EXPIRATION,
		AnnouncementType::SPACE_TARIFF_EXPIRED,
	];

	/** @var array активны статусы для анонсов */
	protected const _ACTIVE_STATUS_LIST = [
		AnnouncementStatus::ACTIVE,
	];

	/**
	 * Возвращает типы анонсов, являющихся блокирующими.
	 *
	 * @return int[]
	 */
	public static function getBlockingTypes():array {

		return static::_BLOCKING_TYPE_LIST;
	}

	/**
	 * Возвращает типы анонсов, которые могут существовать только в единственном числе.
	 *
	 * @return int[]
	 */
	public static function getUniqueTypes():array {

		return static::_UNIQ_TYPE_LIST;
	}

	/**
	 * Возвращает список статусов, в которых анонс считается активным
	 * и может быть показан пользователям.
	 *
	 * @return int[]
	 */
	public static function getActiveStatuses():array {

		return static::_ACTIVE_STATUS_LIST;
	}

	/**
	 * Возвращает поведение уникальности анонса.
	 * Уникальный анонс существует в единственном экземпляре и обновляется при публикации анонса такого же типа.
	 *
	 * @param int $type
	 *
	 * @return bool
	 */
	#[\JetBrains\PhpStorm\Pure]
	public static function isUniqueType(int $type):bool {

		return in_array($type, static::_UNIQ_TYPE_LIST);
	}

	/**
	 * Создает структуру анонса из сырых данных.
	 *
	 * @param array $raw_data
	 *
	 * @return Struct_Db_AnnouncementMain_Announcement
	 */
	public static function makeStructFromRawData(array $raw_data):Struct_Db_AnnouncementMain_Announcement {

		return new Struct_Db_AnnouncementMain_Announcement(
			0,
			$raw_data["is_global"],
			$raw_data["type"],
			$raw_data["status"],
			$raw_data["company_id"],
			$raw_data["priority"],
			time(),
			time(),
			$raw_data["expires_at"],
			$raw_data["resend_repeat_time"],
			$raw_data["receiver_user_id_list"],
			$raw_data["excluded_user_id_list"],
			$raw_data["extra"],
		);
	}
}
