<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Действие создания дефолтных ботов
 *
 * Class Domain_User_Action_Create_BotList
 */
class Domain_User_Action_Create_BotList {

	/**
	 * действие создания дефолтных ботов
	 *
	 * @param array $bot_info_list
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\InvalidPhoneNumber
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 * @throws cs_DamagedActionException
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @long
	 */
	public static function do(array $bot_info_list = []):array {

		if (count($bot_info_list) < 1) {
			$bot_info_list = fromJson(file_get_contents(PIVOT_MODULE_ROOT . "sh/start/bot_info_list.json"));
		}

		$bot_user_id_list = [];
		foreach ($bot_info_list as $bot) {

			$name            = $bot["name"];
			$npc_type        = $bot["npc_type"];
			$avatar_file_map = $bot["avatar_file_map"];
			$user_id         = $bot["user_id"] ?? 0;

			// если вдруг попался npc_type НЕ бота
			if (!Type_User_Main::isBot($npc_type)) {
				throw new ParseFatalException("npc type not a bot");
			}

			// создаем бота в соответствии с типом
			$bot_type = Type_User_Main::getUserType($npc_type);
			if ($bot_type === Type_User_Main::OUTER_BOT) {
				$bot = Domain_User_Action_Create_BotOuter::do("", "", "", "", getIp(), $name, $avatar_file_map, []);
			} else {

				// в зависимости от типа системного бота
				$bot = match ($npc_type) {
					Type_User_Main::NPC_TYPE_SYSTEM_BOT_NOTICE  => Domain_User_Action_Create_Bot::do("", "", "", "", getIp(), $name,
						$avatar_file_map, [], $user_id),
					Type_User_Main::NPC_TYPE_SYSTEM_BOT_REMIND  => Domain_User_Action_Create_RemindBot::do("", "", "", "", getIp(), $name,
						$avatar_file_map, [], $user_id),
					Type_User_Main::NPC_TYPE_SYSTEM_BOT_SUPPORT => Domain_User_Action_Create_SupportBot::do("", "", "", "", getIp(), $name,
						$avatar_file_map, [], $user_id),
					default                                     => throw new ParseFatalException("undefined bot npc_type on create: {$npc_type}"),
				};
			}

			// добавляем id нового бота в конфиг pivot
			Domain_User_Action_AddBotIdToConfig::do($bot->user_id, $npc_type);

			$bot_user_id_list[] = $bot->user_id;
		}

		// ставим аватарки
		Domain_User_Action_UpdateRemindBot::do();
		Domain_User_Action_UpdateSupportBot::do();

		return $bot_user_id_list;
	}
}
