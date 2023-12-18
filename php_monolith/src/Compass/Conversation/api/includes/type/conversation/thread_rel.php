<?php

namespace Compass\Conversation;

/**
 * класс для работы с тредами из одного диалога (conversation_map)
 *
 */
class Type_Conversation_ThreadRel {

	/**
	 * Добавляет информацию о треде для конкретного сообщения
	 *
	 * @throws cs_Message_AlreadyContainsThread
	 * @throws \queryException
	 */
	public static function add(string $conversation_map, string $thread_map, string $message_map, bool $is_thread_hidden_for_all_users):void {

		// получаем block_id сообщения
		$block_id = \CompassApp\Pack\Message\Conversation::getBlockId($message_map);

		// инициализируем extra
		$extra = Type_Conversation_ThreadRel_Extra::initExtra();
		$extra = Type_Conversation_ThreadRel_Extra::setThreadHiddenForAllUsers($extra, $is_thread_hidden_for_all_users);

		Gateway_Db_CompanyConversation_MessageThreadRel::insert($conversation_map, [
			"conversation_map" => $conversation_map,
			"message_map"      => $message_map,
			"thread_map"       => $thread_map,
			"block_id"         => $block_id,
			"extra"            => $extra,
		]);
	}

	/**
	 * Возвращает релейшен треда в массиве к собщению
	 *
	 * Массив имеет следующий вид: <p>
	 * <code>
	 * [
	 *      [<message_map>] => [<thread_map>]
	 * ]
	 * </code>
	 * </p>
	 *
	 *
	 */
	public static function getThreadRelByMessageMap(string $conversation_map, string $message_map):array {

		$thread_rel_list = [];

		try {

			$thread_relation_row = Gateway_Db_CompanyConversation_MessageThreadRel::getOneByMessageMap($conversation_map, $message_map);

			$thread_rel_list[$thread_relation_row->message_map]["thread_map"]                     = $thread_relation_row->thread_map;
			$thread_rel_list[$thread_relation_row->message_map]["thread_hidden_user_list"]        = Type_Conversation_ThreadRel_Extra
				::getHideUserList($thread_relation_row->extra);
			$thread_rel_list[$thread_relation_row->message_map]["is_thread_hidden_for_all_users"] = (int) Type_Conversation_ThreadRel_Extra
				::isThreadHiddenForAllUsers($thread_relation_row->extra);
		} catch (\cs_RowIsEmpty) {
			// ничего не делаем
		}

		return $thread_rel_list;
	}

	/**
	 * Возвращает массив тредов к собщениям пераданными в message_map_list
	 *
	 * Массив имеет следующий вид: <p>
	 * <code>
	 * [
	 *      [<message_map>] => [<thread_map>]
	 * ]
	 * </code>
	 * </p>
	 *
	 *
	 */
	public static function getThreadRelByMessageMapList(string $conversation_map, array $message_map_list):array {

		$thread_rel_list = [];

		$thread_relation_list = Gateway_Db_CompanyConversation_MessageThreadRel::getThreadListByMessageMapList($conversation_map, $message_map_list);
		foreach ($thread_relation_list as $thread_relation_row) {
			$thread_rel_list[$thread_relation_row["message_map"]] = $thread_relation_row["thread_map"];
		}

		return $thread_rel_list;
	}

	/**
	 * Возвращает массив тредов к собщениям из блока
	 *
	 * Массив имеет следующий вид: <p>
	 * <code>
	 * [
	 *      [<message_map>] => [<thread_map>]
	 * ]
	 * </code>
	 * </p>
	 *
	 *
	 */
	public static function getThreadRelByBlockId(string $conversation_map, int $block_id):array {

		$thread_rel_list = [];

		$thread_relation_list = Gateway_Db_CompanyConversation_MessageThreadRel::getThreadListByBlock($conversation_map, $block_id);
		foreach ($thread_relation_list as $thread_relation_row) {
			$thread_rel_list[$thread_relation_row["message_map"]] = $thread_relation_row["thread_map"];
		}

		return $thread_rel_list;
	}

	/**
	 * Возвращает массив тредов к собщениям из списка блоков
	 *
	 * Массив имеет следующий вид: <p>
	 * <code>
	 * [
	 *      [<message_map>] => [<thread_map>]
	 * ]
	 * </code>
	 * </p>
	 *
	 *
	 */
	public static function getThreadRelByBlockList(string $conversation_map, array $block_id_list):array {

		$thread_rel_list = [];

		$thread_relation_list = Gateway_Db_CompanyConversation_MessageThreadRel::getThreadListByBlockList($conversation_map, $block_id_list);
		foreach ($thread_relation_list as $thread_relation_row) {
			$thread_rel_list = self::prepareThreadRelData($thread_rel_list, $thread_relation_row);
		}

		return $thread_rel_list;
	}

	/**
	 * подготавливаем данные связи сообщения и треда
	 */
	public static function prepareThreadRelData(array $thread_rel_list, Struct_Db_CompanyConversation_MessageThreadRel $thread_relation):array {

		$thread_rel_list[$thread_relation->message_map]["thread_map"]                     = $thread_relation->thread_map;
		$thread_rel_list[$thread_relation->message_map]["thread_hidden_user_list"]        = Type_Conversation_ThreadRel_Extra
			::getHideUserList($thread_relation->extra);
		$thread_rel_list[$thread_relation->message_map]["is_thread_hidden_for_all_users"] = (int) Type_Conversation_ThreadRel_Extra
			::isThreadHiddenForAllUsers($thread_relation->extra);

		return $thread_rel_list;
	}
}