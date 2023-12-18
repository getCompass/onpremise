<?php

namespace Compass\Thread;

/**
 * $reaction_count_list – массив вида [реакция1 => количество, реакция2 => количество] для одного сообщения
 * $last_reaction_updated_ms – время последнего обновления реакций для одного сообщения в ms
 */
class Type_Thread_Reaction_Main {

	// получаем последние реакции для диалога
	public static function getMyReactions(string $conversation_map, int $user_id):array {

		$message_block_reaction_list = Gateway_Db_CompanyThread_MessageBlockReactionList::getLastBlocks($conversation_map, 30);

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

		$block_id = \CompassApp\Pack\Message\Thread::getBlockId($message_map);
		try {

			$block_reaction_row = Gateway_Db_CompanyThread_MessageBlockReactionList::getOne($conversation_map, $block_id);
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
	public static function getUserListForReactionList(string $conversation_map, string $message_map, array $reaction_short_name_list):array {

		// получаем uniq для каждой реакции
		$block_id = \CompassApp\Pack\Message\Thread::getBlockId($message_map);
		try {

			$block_reaction_row = Gateway_Db_CompanyThread_MessageBlockReactionList::getOne($conversation_map, $block_id);
		} catch (\cs_RowIsEmpty) {
			return [];
		}

		$reaction_list = $block_reaction_row->getMessageReactionList($message_map);

		$output = [];
		foreach ($reaction_short_name_list as $reaction_short_name) {

			if (isset($reaction_list[$reaction_short_name])) {

				asort($reaction_list[$reaction_short_name]);
				$output[$reaction_short_name] = array_keys($reaction_list[$reaction_short_name]);
			}
		}
		return $output;
	}

	// получаем реакции для сообщений
	public static function getReactionListAndUpdated(string $thread_map, int $block_id, string $message_map):array {

		try {

			$reaction_count_block_row = Gateway_Db_CompanyThread_MessageBlockReactionList::getOne($thread_map, $block_id);
		} catch (\cs_RowIsEmpty) {
			return [[], 0];
		}

		return self::getReactionListAndUpdatedForMessageBlock($reaction_count_block_row, $message_map);
	}

	// получаем реакции для сообщений из блока с реакциями
	public static function getReactionListAndUpdatedForMessageBlock(Struct_Db_CompanyThread_MessageBlockReaction $reaction_count_block_row, string $message_map):array {

		$reaction_list     = [];
		$reaction_last_edited_at = 0;

		$message_reaction_list = $reaction_count_block_row->getMessageReactionList($message_map);
		if (count($message_reaction_list) < 1) {
			return [[], 0];
		}

		foreach ($message_reaction_list as $reaction_name => $user_list) {

			$reaction_list[$reaction_name] = array_keys($user_list);
			foreach ($user_list as $updated_at_ms) {
				$reaction_last_edited_at = max($reaction_last_edited_at, $updated_at_ms);
			}
		}

		// подготавливаем сообщение к форматированию
		return [$reaction_list, $reaction_last_edited_at];
	}

	// получаем реакцию если она существует в списке доступных
	public static function getReactionNameIfExist(string $short_name):string {

		// получаем конфиг
		$conf = \BaseFrame\Conf\Reaction::REACTION_ALL;

		return $conf[$short_name] ?? "";
	}
}