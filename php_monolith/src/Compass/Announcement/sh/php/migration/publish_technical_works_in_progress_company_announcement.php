<?php

namespace Compass\Announcement;

require_once __DIR__ . "/../../../../../../start.php";

/**
 * Class Sh_Php_Publish_Technical_Works_In_Progress_Company_Announcement
 */
class Sh_Php_Migration_Publish_Technical_Works_In_Progress_Company_Announcement {

	protected const _HINT = [
		\Service\AnnouncementTemplate\AnnouncementType::APPLICATION_TECHNICAL_WORKS_IN_PROGRESS => [
			"description"       => "анонс технических работ",
			"extra_description" => [
				"started_at"               => "время начала, тех. работ (timestamp)",
				"app_will_be_available_at" => "когда приложение станет доступно (timestamp)",
			],
		],
	];

	/**
	 * Точка входа.
	 */
	public function exec():void {

		// параметры
		$company_id = Type_Script_InputParser::getArgumentValue("--company_id", Type_Script_InputParser::TYPE_INT);

		$template = \Service\AnnouncementTemplate\TemplateService::createOfType(\Service\AnnouncementTemplate\AnnouncementType::APPLICATION_TECHNICAL_WORKS_IN_PROGRESS);

		$template["is_global"]             = false;
		$template["company_id"]            = $company_id;
		$template["created_at"]            = time();
		$template["updated_at"]            = time();
		$template["announcement_id"]       = 0;
		$template["receiver_user_id_list"] = [];
		$template["excluded_user_id_list"] = [];
		$template["expires_at"]            = 0;
		$template["priority"]              = 0;

		$template = static::_setAnnouncementExtra($template);

		$announcement = Domain_Announcement_Action_Publish::do(new Struct_Db_AnnouncementMain_Announcement(...$template));

		console("announcement_id={$announcement->announcement_id}");
	}

	/**
	 * Устанавливаем данные экстры анонса.
	 *
	 * @param array $template
	 *
	 * @return array
	 */
	protected static function _setAnnouncementExtra(array $template):array {

		$template["extra"]["extra"] = self::_modifyArray($template["extra"]["extra"], "[extra]", static::_HINT[$template["type"]]["extra_description"] ?? []);
		return $template;
	}

	/**
	 * Обновляет массив
	 *
	 * @param array  $data
	 * @param string $prefix
	 *
	 * @return array
	 */
	protected static function _modifyArray(array $data, string $prefix, array $hint = []):array {

		foreach ($data as $k => $v) {

			if (is_array($v)) {

				$data[$k] = static::_modifyArray($v, "{$prefix}[{$k}]", $hint[$k] ?? []);
				continue;
			}

			$data[$k] = $v;
		}

		return $data;
	}
}

(new Sh_Php_Migration_Publish_Technical_Works_In_Progress_Company_Announcement())->exec();