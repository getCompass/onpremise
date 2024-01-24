<?php

namespace Compass\Conversation;

/**
 * обновляем флаг is_allowed для пользовательского бота
 */
class Domain_Conversation_Action_UpdateIsAllowedForUserbot {

	/**
	 * выполняем
	 * @long
	 */
	public static function do(int $userbot_user_id, int $allow_status = ALLOW_STATUS_NEED_CHECK):array {

		// получаем левое меню бота по user_id бота и устанавливаем лимит получения записей за раз
		$count = Gateway_Db_CompanyConversation_UserLeftMenu::getCountLeftMenu($userbot_user_id);
		$limit = 500;

		// проходим по всем записям
		$single_conversation_map_list = [];
		$opponent_left_menu_list      = [];
		$opponent_user_id_list        = [];
		for ($i = 0; $i < ceil($count / $limit); $i++) {

			// получаем записи по id бота
			$offset         = $i * $limit;
			$left_menu_list = Gateway_Db_CompanyConversation_UserLeftMenu::getByOffset($userbot_user_id, $limit, $offset);

			// бежим по всем диалогам бота
			foreach ($left_menu_list as $left_menu_row) {

				// если не относится к single-диалогу с ботом
				if (!Type_Conversation_Meta::isSubtypeOfSingle($left_menu_row["type"])) {
					continue;
				}

				$single_conversation_map_list[] = $left_menu_row["conversation_map"];

				// собираем левое меню оппонентов для бота
				$opponent_left_menu_row = Gateway_Db_CompanyConversation_UserLeftMenu::getOne($left_menu_row["opponent_user_id"], $left_menu_row["conversation_map"]);

				$opponent_left_menu_list[$left_menu_row["conversation_map"]] = $opponent_left_menu_row;
				$opponent_user_id_list[]                                     = $opponent_left_menu_row["user_id"];
			}
		}

		// получаем меты диалога
		$meta_list = Type_Conversation_Meta::getAll($single_conversation_map_list, true);

		// обновляем флаг is_allowed в собранном списке мет
		$set = [
			"allow_status" => $allow_status,
			"updated_at"   => time(),
		];
		Gateway_Db_CompanyConversation_ConversationMetaLegacy::setList($single_conversation_map_list, $set);

		foreach ($meta_list as $meta_row) {

			// очищаем кэш треда
			Type_Phphooker_Main::sendClearThreadMetaCache($meta_row["conversation_map"]);

			if (!isset($opponent_left_menu_list[$meta_row["conversation_map"]])) {
				continue;
			}

			$opponent_left_menu_row = $opponent_left_menu_list[$meta_row["conversation_map"]];

			// размьючиваем диалог
			$set = self::_setUnmutedIfNeed($opponent_left_menu_row);

			// обновляем поле allow_status_alias
			$allow_status_alias = Type_Conversation_Utils::getAllowStatus($allow_status, $meta_row["extra"], $userbot_user_id);

			$set["allow_status_alias"] = $allow_status_alias;
			Domain_User_Action_Conversation_UpdateLeftMenu::do($opponent_left_menu_row["user_id"], $opponent_left_menu_row["conversation_map"], $set);
		}

		return $opponent_user_id_list;
	}

	/**
	 * если чат нужно размьютить
	 *
	 * @throws \parseException
	 */
	protected static function _setUnmutedIfNeed(array $left_menu_row):array {

		// если диалог был замьючен
		$set = [];
		if (isset($left_menu_row["user_id"]) && ($left_menu_row["muted_until"] > 0 || $left_menu_row["is_muted"] > 0)) {

			$set = [
				"is_muted"    => 0,
				"muted_until" => 0,
			];

			// размьючиваем
			Domain_Conversation_Entity_Dynamic::setUnmuted($left_menu_row["conversation_map"], $left_menu_row["user_id"]);

			// шлем эвент что размьютитили чат
			Gateway_Bus_Sender::conversationMutedChanged($left_menu_row["user_id"], $left_menu_row["conversation_map"], 0, 0);
		}

		return $set;
	}
}