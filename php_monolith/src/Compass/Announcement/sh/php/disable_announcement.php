<?php

namespace Compass\Announcement;

require_once __DIR__ . "/../../../../../start.php";

/**
 * Class Sh_Php_DisableAnnouncement
 */
class Sh_Php_DisableAnnouncement {

	protected const _LIMIT_PER_QUERY = 1000;

	/**
	 * Точка входа.
	 */
	public function exec():void {

		$type       = static::_pickType();
		$company_id = static::_pickCompany();

		$status_list = Domain_Announcement_Entity::getActiveStatuses();
		$iteration   = 0;

		/** @var Struct_Db_AnnouncementMain_Announcement[] $announcement_list */
		$announcement_list = [];

		do {

			$offset       = $iteration++ * static::_LIMIT_PER_QUERY;
			$fetched_list = Gateway_Db_AnnouncementMain_Announcement::getListToDisable($type, $company_id, $status_list, static::_LIMIT_PER_QUERY, $offset);

			$announcement_list = array_merge($announcement_list, $fetched_list);
		} while (count($fetched_list) >= static::_LIMIT_PER_QUERY);

		foreach ($announcement_list as $k => $v) {

			$create_data = date("H:m d/m/y", $v->created_at);
			console("отключаем $v->announcement_id созданный {$create_data}?");

			if (!Type_Script_InputHelper::assertConfirm("continue?")) {
				continue;
			}

			Gateway_Db_AnnouncementMain_Announcement::delete($v->announcement_id);
		}
	}

	/**
	 * Предлагает пользователю выбрать тип анонса для создания.
	 *
	 * @return int
	 */
	protected static function _pickType():int {

		console("выберите тип анонса для поиска");
		$known = \Service\AnnouncementTemplate\AnnouncementType::getKnownStringTypes();

		foreach ($known as $string_type => $numeric_type) {
			console("{$numeric_type}: $string_type");
		}

		$input = readline();

		if (!in_array($input, $known)) {
			die("переданный тип анонса не существует");
		}

		return (int) $input;
	}

	/**
	 * Предлагает пользователю компанию для создания анонса.
	 *
	 * @return int
	 */
	protected static function _pickCompany():int {

		console("введи ид компании для поиска анонсов, если нужны глобальные анонсы, введи 0");

		$type = readline();
		$type = $type !== "" ? $type : 0;

		if (!is_numeric($type)) {
			die("passed incorrect company id");
		}

		return (int) $type;
	}
}

(new Sh_Php_DisableAnnouncement())->exec();