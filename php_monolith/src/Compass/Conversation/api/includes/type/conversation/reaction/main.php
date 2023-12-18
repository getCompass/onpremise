<?php

namespace Compass\Conversation;

/**
 * $reaction_count_list – массив вида [реакция1 => количество, реакция2 => количество] для одного сообщения
 * $last_reaction_updated_ms – время последнего обновления реакций для одного сообщения в ms
 */
class Type_Conversation_Reaction_Main {

	// получаем последние реакции для диалога
	public static function getMyReactions(string $conversation_map, int $user_id):array {

		$message_block_reaction_list = Gateway_Db_CompanyConversation_MessageBlockReactionList::getLastBlocks($conversation_map, 30);

		$output = [];
		foreach ($message_block_reaction_list as $message_block_reaction_row) {

			$block_reaction_list = $message_block_reaction_row->getReactionList();
			foreach ($block_reaction_list as $message_map => $reaction_item) {

				// так как json изменяется в go
				// проверка на null потому что так go удаляет записи :(
				if (is_null($reaction_item)) {
					continue;
				}

				$reaction_list = [];
				foreach ($reaction_item as $reaction_name => $updated_at_by_user_id) {

					if (isset($updated_at_by_user_id[$user_id])) {
						$reaction_list[] = $reaction_name;
					}
				}
				$output[] = [
					"message_map"   => (string) $message_map,
					"reaction_list" => (array) $reaction_list,
				];
			}
		}

		return $output;
	}

	// получаем пользователей для одной реакции
	public static function getUserListForReaction(string $conversation_map, string $message_map, string $reaction_short_name):array {

		$block_id = \CompassApp\Pack\Message\Conversation::getBlockId($message_map);
		try {

			$block_reaction_row = Gateway_Db_CompanyConversation_MessageBlockReactionList::getOne($conversation_map, $block_id);
		} catch (\cs_RowIsEmpty) {
			return [];
		}

		$reaction_list = $block_reaction_row->getMessageReactionList($message_map);
		if (!isset($reaction_list[$reaction_short_name])) {
			return [];
		}

		asort($reaction_list[$reaction_short_name]);
		return array_keys($reaction_list[$reaction_short_name]);
	}

	// получаем пользователей для списка реакций
	public static function getUserListForReactionList(string $conversation_map, string $message_map):array {

		// получаем uniq для каждой реакции
		$block_id = \CompassApp\Pack\Message\Conversation::getBlockId($message_map);
		try {

			$block_reaction_row = Gateway_Db_CompanyConversation_MessageBlockReactionList::getOne($conversation_map, $block_id);
		} catch (\cs_RowIsEmpty) {
			return [];
		}

		$reaction_list = $block_reaction_row->getMessageReactionList($message_map);

		return Domain_Conversation_Action_Message_GetFromReactionList::do($reaction_list);
	}

	// получаем реакции для сообщений
	public static function attachReactionList(array $output, array $message):array {

		$message_map = Type_Conversation_Message_Main::getHandler($message)::getMessageMap($message);

		return self::_getActiveMessageReactionList($output, $message_map);
	}

	// получаем список реакций для горячего сообщения
	protected static function _getActiveMessageReactionList(array $output, string $message_map):array {

		$block_id         = \CompassApp\Pack\Message\Conversation::getBlockId($message_map);
		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		[$reaction_user_list, $last_reaction_edited] = Helper_Conversations::prepareReaction($message_map, $conversation_map, $block_id);

		// достаем список реакций и время последнего редактирования
		$output["reaction_user_list"]   = $reaction_user_list;
		$output["last_reaction_edited"] = $last_reaction_edited;

		return $output;
	}

	// получаем реакцию если она существует в списке доступных
	public static function getReactionNameIfExist(string $short_name):string {

		// получаем конфиг
		$conf = \BaseFrame\Conf\Reaction::REACTION_ALL;

		return $conf[$short_name] ?? "";
	}
}