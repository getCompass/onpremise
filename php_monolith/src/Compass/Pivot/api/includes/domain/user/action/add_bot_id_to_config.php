<?php

namespace Compass\Pivot;

/**
 * Действие добавления id бота в конфиг
 *
 * Class Domain_User_Action_AddBotIdToConfig
 */
class Domain_User_Action_AddBotIdToConfig {

	/**
	 * Добавляем id бота в конфиг
	 *
	 * @throws \parseException
	 */
	public static function do(int $bot_user_id, int $npc_type):void {

		// достаем информацию по ботам из конфига
		$config = Type_System_Config::init()->getConf(Domain_User_Entity_Bot::PIVOT_BOT_LIST);

		// получаем ключ для бота по его npc_type
		$bot_key = Domain_User_Entity_Bot::getConfigKeyByNpcType($npc_type);

		if (isset($config["bot_id_list_by_type"][$bot_key])) {
			$bot_user_id_list = $config["bot_id_list_by_type"][$bot_key];
		} else {

			$config["bot_id_list_by_type"][$bot_key] = [];
			$bot_user_id_list                        = $config["bot_id_list_by_type"][$bot_key];
		}

		// добавляем в список id нового бота
		array_push($bot_user_id_list, $bot_user_id);

		// сохраняем обновленный конфиг
		$config["bot_id_list_by_type"][$bot_key] = $bot_user_id_list;
		Type_System_Config::init()->set(Domain_User_Entity_Bot::PIVOT_BOT_LIST, $config);
	}
}
