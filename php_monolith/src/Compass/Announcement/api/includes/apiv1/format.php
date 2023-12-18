<?php

namespace Compass\Announcement;

use JetBrains\PhpStorm\ArrayShape;
use Service\AnnouncementTemplate\AnnouncementType;

/**
 * класс для форматирование сущностей под формат API
 *
 * в коде мы оперируем своими структурами и понятиями
 * к этому классу обращаемся строго отдачей результата в API
 * для форматирования стандартных сущностей
 */
class Apiv1_Format {

	/**
	 * Конвертирует анонс в понятный для клиентов формат.
	 *
	 * @param Struct_Db_AnnouncementMain_Announcement $announcement
	 *
	 * @return array
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["announcement_id" => "int", "is_global" => "int", "type" => "mixed", "company_id" => "int", "priority" => "int", "expires_at" => "int", "retry_period" => "int", "extra" => "array"])]
	public static function announcement(Struct_Db_AnnouncementMain_Announcement $announcement):array {

		$data = (array) $announcement;

		return [
			"announcement_id" => (int) $data["announcement_id"],
			"is_global"       => (int) $data["is_global"],
			"type"            => AnnouncementType::resolveTextType($data["type"]),
			"company_id"      => (int) $data["company_id"],
			"priority"        => (int) $data["priority"],
			"expires_at"      => (int) $data["expires_at"],
			"retry_period"    => 60,
			"extra"           => (array) static::_formatAnnouncementExtra($data["type"], $data["extra"]["extra"] ?? []),
		];
	}

	/**
	 * Получает форматированную экстру для анонса по его типу и данным экстры.
	 *
	 * @param int   $type
	 * @param array $data
	 *
	 * @return array
	 */
	protected static function _formatAnnouncementExtra(int $type, array $data):array {

		// форматируем экстру при наличии
		$data = static::_formatExtraMeta($data);

		return match ($type) {

			// тестовые анонсы
			AnnouncementType::TEST_GLOBAL_BLOCKING,
			AnnouncementType::TEST_GLOBAL_NOTIFYING,
			AnnouncementType::TEST_COMPANY_NOTIFYING,
			AnnouncementType::TEST_COMPANY_BLOCKING => [
				"unique" => (string) $data["unique"],
			],

			AnnouncementType::APPLICATION_TECHNICAL_WORKS_IN_PROGRESS => static::_formatApplicationTechnicalWorksInProgressExtra($data),
			AnnouncementType::COMPANY_TECHNICAL_WORKS_IN_PROGRESS => static::_formatCompanyTechnicalWorksInProgressExtra($data),

			default => $data,
		};
	}

	/**
	 * Форматирует экстра-мету для анонса, если она есть.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	protected static function _formatExtraMeta(array $data):array {

		if (!isset($data["meta"])) {
			return $data;
		}

		$formatted_meta = [
			"platform"               => (string) $data["meta"]["platform"],
			"supported_code_version" => (string) $data["meta"]["supported_code_version"],
		];

		$data["meta"] = $formatted_meta;
		return $data;
	}

	/**
	 * Форматирует экстру для анонса технических работ.
	 *
	 * @param array $data
	 *
	 * @return int[]
	 */
	#[ArrayShape(["started_at" => "int", "app_will_be_available_at" => "int"])]
	protected static function _formatApplicationTechnicalWorksInProgressExtra(array $data):array {

		// здесь немного логики, но она клиентская
		// если время таймера истекло, то всегда нужно отдавать 15 минут
		if ($data["app_will_be_available_at"] <= time()) {
			$data["app_will_be_available_at"] = time() + 60 * 15;
		}

		return [
			"started_at"               => (int) $data["started_at"],
			"app_will_be_available_at" => (int) $data["app_will_be_available_at"],
		];
	}

	/**
	 * Форматирует экстру для анонса компанейских технических работ.
	 *
	 * @param array $data
	 *
	 * @return int[]
	 */
	#[ArrayShape(["started_at" => "int", "will_be_available_at" => "int"])]
	protected static function _formatCompanyTechnicalWorksInProgressExtra(array $data):array {

		// здесь немного логики, но она клиентская
		// если время таймера истекло, то всегда нужно отдавать 15 минут
		if ($data["will_be_available_at"] <= time()) {
			$data["will_be_available_at"] = time() + 60 * 15;
		}

		return [
			"started_at"           => (int) $data["started_at"],
			"will_be_available_at" => (int) $data["will_be_available_at"],
		];
	}
}
