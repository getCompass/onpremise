<?php

namespace Compass\Conversation;

/**
 * класс для выполнения различных действий над сообщениями
 */
class Type_Message_Utils {

	##########################################################
	# region обработка сообщений без склеивания сообщений
	# (например, для рабочих часов)
	##########################################################

	// подготавливаем сообщение к пересылке
	public static function prepareMessageForwarding(array $message, bool $is_need_special_actions = false):array {

		// получаем тип сообщения
		$message_type = Type_Conversation_Message_Main::getHandler($message)::getType($message);
		switch ($message_type) {

			// если сообщение является файлом
			case CONVERSATION_MESSAGE_TYPE_FILE:
			case CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_FILE:

				if ($is_need_special_actions) {
					return self::_prepareForRepostQuoteRemindIfMessageFile($message);
				}

				return self::_prepareForwardingIfConversationFile($message);

			// если сообщение является репостом
			case CONVERSATION_MESSAGE_TYPE_REPOST:
			case CONVERSATION_MESSAGE_TYPE_THREAD_REPOST:

				if ($is_need_special_actions) {
					return self::_prepareForRepostQuoteRemindIfMessageRepost($message);
				}

				return self::_prepareForwardingIfConversationRepost($message);

			// если сообщение является цитатой
			case CONVERSATION_MESSAGE_TYPE_QUOTE:
			case CONVERSATION_MESSAGE_TYPE_MASS_QUOTE:

				if ($is_need_special_actions) {
					return self::_prepareForRepostQuoteRemindIfMessageQuote($message);
				}

				return self::_prepareForwardingIfConversationQuote($message);

			default:
				return $message;
		}
	}

	// подготавливаем сообщение-файл
	protected static function _prepareForwardingIfConversationFile(array $message):array {

		// устанавливаем новый file_uid для файла
		return Type_Conversation_Message_Main::getHandler($message)::setNewFileUid($message);
	}

	// подготавливаем сообщение-репост
	protected static function _prepareForwardingIfConversationRepost(array $message):array {

		// если имеется родитель у репоста, то достаем его и убираем из структуры сообщения
		$parent_message_data = [];
		if (isset($message["data"]["parent_message_data"])) {

			$parent_message_data = $message["data"]["parent_message_data"];
			unset($message["data"]["parent_message_data"]);
		}

		// получаем все репостнутые сообщения
		$reposted_message_list = Type_Conversation_Message_Main::getHandler($message)::getRepostedMessageList($message);
		$reposted_message_list = self::_prepareChildMessageListForwarding($reposted_message_list, $parent_message_data);

		// добавляем обновленный список репостнутых сообщений в репост
		return Type_Conversation_Message_Main::getHandler($message)::setRepostedMessageList($reposted_message_list, $message);
	}

	// подготавливаем сообщение-цитату
	protected static function _prepareForwardingIfConversationQuote(array $message):array {

		// если имеется родитель у цитаты, то достаем его и убираем из структуры сообщения
		$parent_message_data = [];
		if (isset($message["data"]["parent_message_data"])) {

			$parent_message_data = $message["data"]["parent_message_data"];
			unset($message["data"]["parent_message_data"]);
		}

		// получаем все процитированные сообщения
		$quoted_message_list = Type_Conversation_Message_Main::getHandler($message)::getQuotedMessageList($message);
		$quoted_message_list = self::_prepareChildMessageListForwarding($quoted_message_list, $parent_message_data);

		// добавляем обновленный список процитированных сообщений в цитату
		return Type_Conversation_Message_Main::getHandler($message)::setQuotedMessageList($quoted_message_list, $message);
	}

	// подготавливаем сообщения из процитированных/репостнутых
	protected static function _prepareChildMessageListForwarding(array $message_list, array $parent_message_data):array {

		// если имеется родитель, то добавляем его в сообщения
		$message_list = self::_addParentIfExist($message_list, $parent_message_data);

		// для каждого сообщения
		foreach ($message_list as $k => $v) {
			$message_list[$k] = self::prepareMessageForwarding($v);
		}

		return $message_list;
	}

	# endregion
	##########################################################

	##########################################################
	# region обработка сообщений со склеиванием сообщений - для репоста, цитаты, напоминания
	##########################################################

	// подготавливаем сообщение к репосту/цитате/напоминанию
	public static function prepareMessageForRepostQuoteRemind(array $message):array {

		// получаем тип сообщения
		$message_type = Type_Conversation_Message_Main::getHandler($message)::getType($message);
		switch ($message_type) {

			// если сообщение является файлом
			case CONVERSATION_MESSAGE_TYPE_FILE:
			case CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_FILE:
				return self::_prepareForRepostQuoteRemindIfMessageFile($message);

			// если сообщение является репостом
			case CONVERSATION_MESSAGE_TYPE_REPOST:
			case CONVERSATION_MESSAGE_TYPE_THREAD_REPOST:
				return self::_prepareForRepostQuoteRemindIfMessageRepost($message);

			// если сообщение является цитатой
			case CONVERSATION_MESSAGE_TYPE_QUOTE:
			case CONVERSATION_MESSAGE_TYPE_MASS_QUOTE:
				return self::_prepareForRepostQuoteRemindIfMessageQuote($message);

			default:
				return $message;
		}
	}

	// подготавливаем сообщение-файл
	protected static function _prepareForRepostQuoteRemindIfMessageFile(array $message):array {

		// устанавливаем новый file_uid для файла
		return Type_Conversation_Message_Main::getLastVersionHandler()::setNewFileUid($message);
	}

	// подготавливаем сообщение-репост
	protected static function _prepareForRepostQuoteRemindIfMessageRepost(array $message):array {

		// если имеется родитель у репоста, то достаем его и убираем из структуры сообщения
		$parent_message_data = [];
		if (isset($message["data"]["parent_message_data"])) {

			$parent_message_data = $message["data"]["parent_message_data"];
			unset($message["data"]["parent_message_data"]);
		}

		// получаем все репостнутые сообщения
		$reposted_message_list = Type_Conversation_Message_Main::getLastVersionHandler()::getRepostedMessageList($message);

		// обновляем список сообщений репоста
		$reposted_message_list = self::_upgradeMessageList($reposted_message_list, $parent_message_data);

		// добавляем обновленный список репостнутых сообщений в репост
		return Type_Conversation_Message_Main::getLastVersionHandler()::setRepostedMessageList($reposted_message_list, $message);
	}

	// подготавливаем сообщение-цитату
	protected static function _prepareForRepostQuoteRemindIfMessageQuote(array $message):array {

		// если имеется родитель у цитаты, то достаем его и убираем из структуры сообщения
		$parent_message_data = [];
		if (isset($message["data"]["parent_message_data"])) {

			$parent_message_data = $message["data"]["parent_message_data"];
			unset($message["data"]["parent_message_data"]);
		}

		// получаем все процитированные сообщения
		$quoted_message_list = Type_Conversation_Message_Main::getLastVersionHandler()::getQuotedMessageList($message);

		// обновляем список сообщений цитаты
		$quoted_message_list = self::_upgradeMessageList($quoted_message_list, $parent_message_data);

		// добавляем обновленный список процитированных сообщений в цитату
		return Type_Conversation_Message_Main::getLastVersionHandler()::setQuotedMessageList($quoted_message_list, $message);
	}

	// обновляем список сообщений репоста/цитаты
	protected static function _upgradeMessageList(array $message_list, array $parent_message_data):array {

		// если имеется родитель, то добавляем его в сообщения
		$message_list = self::_addParentIfExist($message_list, $parent_message_data);

		// меняем список сообщений, если среди них нашлось сообщение типа repost, file or quote
		$message_list = Type_Conversation_Message_Main::getLastVersionHandler()::doAdaptationIfIssetRepostOrFileOrQuote($message_list);

		// если текст отсутствует, то просто убираем его из сообщений
		return Type_Conversation_Message_Main::getLastVersionHandler()::removeEmptyMessageFromMessageList($message_list);
	}

	# endregion
	##########################################################

	##########################################################
	# region обработка сообщений со склеиванием сообщений (Version 2)
	# (например, для репоста/цитирования)
	##########################################################

	// подготавливаем сообщение к репосту/цитате
	public static function prepareMessageForRepostOrQuoteV2(array $message, array $prepared_message_list):array {

		// для нумерации каждого сообщения
		$index = count($prepared_message_list) + 1;

		// получаем тип сообщения
		$message_type = Type_Conversation_Message_Main::getHandler($message)::getType($message);
		switch ($message_type) {

			// если сообщение является файлом
			case CONVERSATION_MESSAGE_TYPE_FILE:
			case CONVERSATION_MESSAGE_TYPE_THREAD_REPOST_ITEM_FILE:
				$prepared_message_list = self::_prepareForRepostOrQuoteIfMessageFileV2($message, $message_type, $prepared_message_list, $index);
				return self::_sortedMessageList($prepared_message_list);

			// если сообщение является репостом
			case CONVERSATION_MESSAGE_TYPE_REPOST:
			case CONVERSATION_MESSAGE_TYPE_THREAD_REPOST:
				$prepared_message_list = self::_prepareForRepostOrQuoteIfMessageRepostV2($message, $message_type, $prepared_message_list, $index);
				return self::_sortedMessageList($prepared_message_list);

			// если сообщение является цитатой
			case CONVERSATION_MESSAGE_TYPE_QUOTE:
			case CONVERSATION_MESSAGE_TYPE_MASS_QUOTE:
				$prepared_message_list = self::_prepareForRepostOrQuoteIfMessageQuoteV2($message, $message_type, $prepared_message_list, $index);
				return self::_sortedMessageList($prepared_message_list);

			default:
				$prepared_message_list[] = self::_makeOutputPreparedMessageList($message, $message_type, $message, $index);
				return self::_sortedMessageList($prepared_message_list);
		}
	}

	// подготавливаем сообщение-файл
	protected static function _prepareForRepostOrQuoteIfMessageFileV2(array $message, int $message_type, array $prepared_message_list, int $index):array {

		// устанавливаем новый file_uid для файла
		$message = Type_Conversation_Message_Main::getHandler($message)::setNewFileUid($message);

		$prepared_message_list[] = self::_makeOutputPreparedMessageList($message, $message_type, $message, $index);

		return $prepared_message_list;
	}

	// подготавливаем сообщение-репост
	protected static function _prepareForRepostOrQuoteIfMessageRepostV2(array $message, int $message_type, array $prepared_message_list, int $index):array {

		// если имеется родитель у репоста, то достаем его и убираем из структуры сообщения
		$parent_message_data = [];
		if (isset($message["data"]["parent_message_data"])) {

			$parent_message_data = $message["data"]["parent_message_data"];
			unset($message["data"]["parent_message_data"]);
		}

		// получаем все репостнутые сообщения
		$reposted_message_list = Type_Conversation_Message_Main::getHandler($message)::getRepostedMessageList($message);

		$reposted_message_list = self::_upgradeMessageListV2($reposted_message_list, $message, $parent_message_data, $index);

		// компонуем сообщения
		foreach ($reposted_message_list as $_ => $reposted_message) {

			$prepared_message_list[] = self::_makeOutputPreparedMessageList($reposted_message, $message_type, $message, $index);
			$index++;
		}

		return $prepared_message_list;
	}

	// подготавливаем сообщение-цитату
	protected static function _prepareForRepostOrQuoteIfMessageQuoteV2(array $message, int $message_type, array $prepared_message_list, int $index):array {

		// если имеется родитель у цитаты, то достаем его и убираем из структуры сообщения
		$parent_message_data = [];
		if (isset($message["data"]["parent_message_data"])) {

			$parent_message_data = $message["data"]["parent_message_data"];
			unset($message["data"]["parent_message_data"]);
		}

		// получаем все процитированные сообщения
		$quoted_message_list = Type_Conversation_Message_Main::getHandler($message)::getQuotedMessageList($message);

		$quoted_message_list = self::_upgradeMessageListV2($quoted_message_list, $message, $parent_message_data, $index);

		// компонуем сообщения
		foreach ($quoted_message_list as $_ => $quoted_message) {

			$prepared_message_list[] = self::_makeOutputPreparedMessageList($quoted_message, $message_type, $message, $index);
			$index++;
		}

		return $prepared_message_list;
	}

	// обновляем список сообщений репоста/цитаты
	protected static function _upgradeMessageListV2(array $message_list, array $message, array $parent_message_data, int $index):array {

		// если имеется родитель, то добавляем его в сообщения
		$message_list = self::_addParentIfExist($message_list, $parent_message_data);

		// меняем список сообщений, если среди них нашлось сообщение типа repost, file or quote
		$message_list = Type_Conversation_Message_Main::getLastVersionHandler()::doAdaptationIfIssetRepostOrFileOrQuote($message_list, $index);

		// если текст отсутствует, то просто убираем его из сообщений
		$message_list = Type_Conversation_Message_Main::getLastVersionHandler()::removeEmptyMessageFromMessageList($message_list);

		// если цитата/репост подписана текстом, добавляем сообщение пустышку, для правильного чанкования
		return self::_addEmptyMessageIfExistTextRepostOrQuote($message_list, $message);
	}

	// добавляем пустое сообщение в список, если есть текст у репоста/цитаты
	protected static function _addEmptyMessageIfExistTextRepostOrQuote(array $message_list, array $message):array {

		$empty_message["type"] = Helper_Conversations::MESSAGE_TYPE_EMPTY;

		if (isset($message["data"]["text"]) && $message["data"]["text"] !== "" && $message["type"] === CONVERSATION_MESSAGE_TYPE_REPOST) {
			array_unshift($message_list, $empty_message);
		}

		if (isset($message["data"]["text"]) && $message["data"]["text"] !== "" && $message["type"] === CONVERSATION_MESSAGE_TYPE_THREAD_REPOST) {
			array_unshift($message_list, $empty_message);
		}

		if (isset($message["data"]["text"]) && $message["data"]["text"] !== "" && $message["type"] === CONVERSATION_MESSAGE_TYPE_QUOTE) {
			array_unshift($message_list, $empty_message);
		}

		if (isset($message["data"]["text"]) && $message["data"]["text"] !== "" && $message["type"] === CONVERSATION_MESSAGE_TYPE_MASS_QUOTE) {
			array_unshift($message_list, $empty_message);
		}

		return $message_list;
	}

	// формируем ответ подготовленных сообщений
	protected static function _makeOutputPreparedMessageList(array $message, int $type_message, array $parent_message, int $index):array {

		return [
			"index"          => $index,
			"message"        => $message,
			"type"           => $type_message,
			"parent_message" => $parent_message,
		];
	}

	// сортируем message_list
	protected static function _sortedMessageList(array $prepared_message_list):array {

		// сортируем массив
		uasort($prepared_message_list, function(array $a, array $b) {

			return $a["index"] <=> $b["index"];
		});

		return $prepared_message_list;
	}

	# endregion
	##########################################################

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// добавляем родителя в список сообщений репоста/цитаты, если имеется
	protected static function _addParentIfExist(array $message_list, array $parent_message):array {

		if (count($parent_message) > 0) {
			array_unshift($message_list, $parent_message);
		}

		return $message_list;
	}
}