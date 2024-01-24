<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-структура для таблицы company_thread.message_block_reaction_list
 */
class Struct_Db_CompanyThread_MessageBlockReaction {

	/**
	 * @param string $thread_map
	 * @param int    $block_id
	 * @param int    $created_at
	 * @param int    $updated_at
	 * @param array  $reaction_data
	 * // {
	 * //  "version": 1,
	 * //  "message_reaction_list": {
	 * //    "{message_map}": {
	 * //      "{short_name}": [
	 * //        {
	 * //          "{user_id}": "{updated_at_ms}"
	 * //        }
	 * //      ]
	 * //    }
	 * //  }
	 * //}
	 */
	public function __construct(
		public string $thread_map,
		public int    $block_id,
		public int    $created_at,
		public int    $updated_at,
		public array  $reaction_data,
	) {

	}

	// получаем список реакций к сообщению
	public function getMessageReactionList(string $message_map):array {

		if (!isset($this->reaction_data["version"]) || $this->reaction_data["version"] != 1) {
			throw new ParseFatalException("version message not valid");
		}

		// если записи нет - ок
		// проверка на null потому что так go удаляет записи :(
		if (!isset($this->reaction_data["message_reaction_list"][$message_map]) ||
			is_null($this->reaction_data["message_reaction_list"][$message_map])) {

			return [];
		}
		return $this->reaction_data["message_reaction_list"][$message_map];
	}

	// получаем список реакций к сообщению
	public function getReactionList():array {

		if (!isset($this->reaction_data["version"]) || $this->reaction_data["version"] != 1) {
			throw new ParseFatalException("version no valid");
		}

		return $this->reaction_data["message_reaction_list"];
	}
}