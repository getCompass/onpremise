<?php

namespace Compass\Announcement;

require_once __DIR__ . "/../../../../../start.php";

/**
 * Class Sh_Php_ShowActive
 */
class Sh_Php_ShowActive {

	protected const _LIMIT_PER_QUERY = 1000;

	/**
	 * Точка входа.
	 */
	public function exec():void {

		$company_id = static::_pickCompany();

		$status_list = Domain_Announcement_Entity::getActiveStatuses();
		$iteration   = 0;

		do {

			$offset       = $iteration++ * static::_LIMIT_PER_QUERY;
			$fetched_list = Gateway_Db_AnnouncementMain_Announcement::getActiveList($company_id, $status_list, static::_LIMIT_PER_QUERY, $offset);

			foreach ($fetched_list as $k => $v) {

				$create_data = date("H:m d/m/y", $v->created_at);
				$end_data    = $v->expires_at > 0 ? date("H:m d/m/y", $v->expires_at) : "никогда";
				$type        = \Service\AnnouncementTemplate\AnnouncementType::resolveTextType($v->type);
				$receiver    = count($v->receiver_user_id_list) == 0 ? "все " : implode(", ", $v->receiver_user_id_list);
				$excluded    = count($v->excluded_user_id_list) != 0 ? "кроме " . implode(", ", $v->excluded_user_id_list) : "";

				console("$v->announcement_id — {$type} созданный {$create_data} истекает {$end_data}, получатели: {$receiver} {$excluded}");
			}
		} while (count($fetched_list) >= static::_LIMIT_PER_QUERY);
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

(new Sh_Php_ShowActive())->exec();