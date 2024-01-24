<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * хелпер для сообщений в диалогах
 * Class Helper_Messages
 */
class Helper_Messages {

	/**
	 * метод для получения всех объектов переданного массива сообщений из разных диалогов, с добавлением реакций
	 *
	 * @return array[]
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getMessageDataListFromAnotherConversationsAttachReaction(int $user_id, array $message_map_list, bool $is_need_check_permission = true):array {

		[$message_data_list, $not_access_message_map_list] = self::getMessageDataListFromAnotherConversations(
			$user_id, $message_map_list, $is_need_check_permission);

		$thread_rel_list                   = [];
		$block_id_list_by_conversation_map = [];

		foreach ($message_data_list as $data) {
			$block_id_list_by_conversation_map[$data["conversation_map"]][] = $data["block_id"];
		}

		foreach ($block_id_list_by_conversation_map as $conversation_map => $block_id_list) {

			$conversation_thread_list = Type_Conversation_ThreadRel::getThreadRelByBlockList($conversation_map, $block_id_list);
			$thread_rel_list          = array_merge($thread_rel_list, $conversation_thread_list);
		}

		foreach ($message_data_list as $key => $data) {

			$conversation_map = $data["conversation_map"];
			$message_map      = $data["message_map"];

			// прикрепляем реакции к сообщению
			$message_data_list[$key] = self::_attachReaction($message_map, $conversation_map, $data["block_id"], $data["message"]);
		}

		return [$message_data_list, $not_access_message_map_list, $thread_rel_list];
	}

	/**
	 * крепим реакции к сообщению при необходимости
	 *
	 * @throws \parseException
	 */
	protected static function _attachReaction(string $message_map, string $conversation_map, int $block_id, array $message):array {

		[$reaction_user_list, $last_reaction_edited] = Helper_Conversations::prepareReaction($message_map, $conversation_map, $block_id);

		// добавляем реакции к сообщению
		return [
			"message"              => $message,
			"reaction_user_list"   => $reaction_user_list,
			"last_reaction_edited" => $last_reaction_edited,
		];
	}

	/**
	 * Метод для получения всех объектов переданного массива сообщений из разных диалогов
	 *
	 * @return array[]
	 *
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \cs_UnpackHasFailed
	 */
	public static function getMessageDataListFromAnotherConversations(int $user_id, array $message_map_list, bool $is_need_check_permission = true):array {

		// достаем conversation_map сообщения и сортируем все сообщения по диалогам
		$message_map_list_by_conversation = self::_getMessageMapListByConversation($message_map_list);

		// получаем список диалогов, откуда наши сообщения
		$conversation_map_list = array_keys($message_map_list_by_conversation);

		// получаем список dynamic каждого из диалогов
		$meta_list_by_conversation    = self::_getMetaListByConversation($conversation_map_list, $is_need_check_permission);
		$dynamic_list_by_conversation = self::_getDynamicListByConversation($conversation_map_list);

		$block_row_list_by_conversation = self::getDataRowListByConversation($message_map_list_by_conversation, $dynamic_list_by_conversation);

		$message_info_list = self::_getMessageListFromMapOfMessage($block_row_list_by_conversation, $message_map_list_by_conversation, $dynamic_list_by_conversation);

		[$message_data_list, $not_access_message_map_list] = self::_getMessageDataAndNotAccessList(
			$message_info_list, $is_need_check_permission, $meta_list_by_conversation, $user_id, $dynamic_list_by_conversation);

		return [$message_data_list, $not_access_message_map_list];
	}

	/**
	 * получаем список map сообщений, сортированный по диалогу каждого из сообщений
	 *
	 * @throws \cs_UnpackHasFailed
	 */
	protected static function _getMessageMapListByConversation(array $message_map_list):array {

		$message_map_list_by_conversation = [];

		foreach ($message_map_list as $v) {

			// получаем map диалога, откуда сообщение
			$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($v);

			// каждое сообщение привязываем к его диалогу
			$message_map_list_by_conversation[$conversation_map][] = $v;
		}

		return $message_map_list_by_conversation;
	}

	/**
	 * получаем сортированный список meta диалогов, если надо
	 *
	 */
	protected static function _getMetaListByConversation(array $conversation_map_list, bool $is_need_check_conversation_membership):array {

		if (!$is_need_check_conversation_membership) {
			return [];
		}

		$conversation_meta_list = Type_Conversation_Meta::getAll($conversation_map_list);

		// формирует ответ по ключу
		$output = [];

		foreach ($conversation_meta_list as $item) {
			$output[$item["conversation_map"]] = $item;
		}

		return $output;
	}

	/**
	 * получаем сортированный список dynamic диалогов
	 *
	 */
	protected static function _getDynamicListByConversation(array $conversation_map_list):array {

		$dynamic_list = [];

		foreach ($conversation_map_list as $v) {

			// если для диалога уже получали dynamic
			if (array_key_exists($v, $dynamic_list)) {
				continue;
			}

			// получаем dynamic диалога и добавляем в массив
			$dynamic_list[$v] = Domain_Conversation_Entity_Dynamic::get($v);
		}

		return $dynamic_list;
	}

	/**
	 * получаем данные со списком id блоков сообщений
	 *
	 * @throws \parseException
	 */
	protected static function getDataRowListByConversation(array $message_map_list_by_conversation, array $dynamic_row_by_conversation):array {

		// делаем необходимые запросы для получения блоков каждого из диалогов
		$block_row_list_by_conversation = [];

		foreach ($message_map_list_by_conversation as $conversation_map => $message_map_list) {

			[$block_row_list] = Domain_Conversation_Entity_Message_Block_Get::getBlockListRowByMessageMapList(
				$conversation_map,
				$dynamic_row_by_conversation[$conversation_map],
				$message_map_list
			);

			$block_row_list_by_conversation[$conversation_map] = $block_row_list;
		}

		return $block_row_list_by_conversation;
	}

	/**
	 * получаем все о сообщении через message map
	 *
	 * @throws \returnException
	 * @throws cs_UnpackHasFailed
	 */
	protected static function _getMessageListFromMapOfMessage(array $block_row_list_by_conversation, array $message_map_list_by_conversation, array $dynamic_list):array {

		$message_info_list = [];
		foreach ($message_map_list_by_conversation as $conversation_map => $message_map_list) {

			foreach ($message_map_list as $message_map) {

				$block_id = \CompassApp\Pack\Message\Conversation::getBlockId($message_map);
				$message  = self::_tryMakeMessageDataItem($message_map, $block_row_list_by_conversation[$conversation_map], $block_id);

				$message_info_list[] = [
					"message"          => $message,
					"block_id"         => $block_id,
					"is_active"        => Domain_Conversation_Entity_Message_Block_Main::isActive($dynamic_list[$conversation_map], $block_id),
					"message_map"      => $message_map,
					"conversation_map" => $conversation_map,
					"dynamic_row"      => $dynamic_list[$conversation_map],
				];
			}
		}

		return $message_info_list;
	}

	/**
	 * получить output из message
	 *
	 * @throws \parseException
	 */
	protected static function _getMessageDataAndNotAccessList(array $message_info_list, bool $is_need_check_permission, array $meta_list_by_conversation, int $user_id, array $dynamic_list_by_conversation):array {

		$not_access_message_map_list = [];
		$message_data_list           = [];

		foreach ($message_info_list as $data) {

			if ($is_need_check_permission) {

				$conversation_map   = $data["conversation_map"];
				$users              = $meta_list_by_conversation[$conversation_map]["users"];
				$is_have_permission = self::_isHavePermissionOnMessage($user_id, $data["message"], $dynamic_list_by_conversation[$conversation_map], $users);

				if (!$is_have_permission) {

					$not_access_message_map_list[] = $data["message_map"];
					continue;
				}
			}

			$message_data_list[] = $data;
		}

		return [$message_data_list, $not_access_message_map_list];
	}

	/**
	 * проверяем есть ли права на сообщения у пользователя
	 *
	 * @throws \parseException
	 */
	protected static function _isHavePermissionOnMessage(int $user_id, array $message, array $dynamic_row, array $users):bool {

		if (!Type_Conversation_Meta_Users::isMember($user_id, $users)) {
			return false;
		}

		// если сообщение было скрыто пользователем
		if (Type_Conversation_Message_Main::getHandler($message)::isMessageHiddenForUser($message, $user_id)) {
			return false;
		}

		$clear_until = Domain_Conversation_Entity_Dynamic::getClearUntil($dynamic_row["user_clear_info"], $dynamic_row["conversation_clear_info"], $user_id);

		if (Type_Conversation_Message_Main::getHandler($message)::getCreatedAt($message) < $clear_until) {
			return false;
		}

		return true;
	}

	/**
	 * попробовать сделать сообщение
	 *
	 * @throws \returnException
	 */
	protected static function _tryMakeMessageDataItem(string $message_map, array $block_list, int $block_id):array {

		// проверяем что блок в котором должно лежать сообщение есть среди полученных
		if (!isset($block_list[$block_id])) {
			throw new ReturnFatalException("message block not exist");
		}

		$block_row = $block_list[$block_id];

		return Domain_Conversation_Entity_Message_Block_Message::get($message_map, $block_row);
	}
}