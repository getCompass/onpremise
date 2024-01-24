<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

require_once __DIR__ . "/../../../../../start.php";

/**
 * класс для создания ботов
 */
class Start_Create_System_Bot_List {

	/**
	 * выполняем
	 */
	public static function do():void {

		$bot_info_list = fromJson(file_get_contents(PIVOT_MODULE_ROOT . "sh/start/bot_info_list.json"));

		try {

			$info_list = Gateway_Bus_PivotCache::getUserListInfo(array_column($bot_info_list, "user_id"));
			if (count($info_list) > 0) {
				return;
			}
		} catch (cs_UserNotFound) {
		}

		$bot_user_id_list = Domain_User_Action_Create_BotList::do($bot_info_list);
		if (count($bot_user_id_list) < 1) {

			console("Не смогли создать ботов");
			return;
		}

		console("Системные боты созданы с идентификаторами:");
		console(implode(", ", $bot_user_id_list));
	}
}

if (ServerProvider::isCi()) {
	return;
}

// приступаем к созданию системных ботов
Start_Create_System_Bot_List::do();