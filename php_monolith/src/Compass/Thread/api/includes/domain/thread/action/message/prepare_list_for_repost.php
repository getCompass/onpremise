<?php

namespace Compass\Thread;

/**
 * Действие для подготовки списка сообщений для репоста
 */
class Domain_Thread_Action_Message_PrepareListForRepost {

	/**
	 * Подготовить список сообщений к репосту
	 *
	 * @param array  $message_map_list
	 * @param string $from_thread_map
	 *
	 * @return array
	 * @throws Domain_Thread_Exception_Message_IsDuplicated
	 * @throws Domain_Thread_Exception_Message_IsFromDifferentSource
	 * @throws Domain_Thread_Exception_Message_IsNotFromThread
	 * @throws Domain_Thread_Exception_Message_RepostLimitExceeded
	 */
	public static function do(array $message_map_list, string $from_thread_map):array {

		// проверяем что список сообщений уникален
		$message_map_list_uniq = array_unique($message_map_list);
		if (count($message_map_list_uniq) != count($message_map_list)) {
			throw new Domain_Thread_Exception_Message_IsDuplicated("message in list duplicated");
		}

		$thread_map_list = [];
		foreach ($message_map_list as $v) {

			if (!\CompassApp\Pack\Message::isFromThread($v)) {
				throw new Domain_Thread_Exception_Message_IsNotFromThread("message not from thread");
			}

			// проверяем что сообщение принадлежит данному треду
			if (\CompassApp\Pack\Message\Thread::getThreadMap($v) != $from_thread_map) {
				throw new Domain_Thread_Exception_Message_NotExistThread("message from another thread");
			}

			// проверяем, что сообщения из одного треда
			$thread_map                   = \CompassApp\Pack\Message\Thread::getThreadMap($v);
			$thread_map_list[$thread_map] = true;
			if (count($thread_map_list) > 1) {

				throw new Domain_Thread_Exception_Message_IsFromDifferentSource("one of the messages is from different thread");
			}
		}

		// если сообщений больше 100 - сворачиваемся
		if (count($message_map_list) > Type_Thread_Message_Handler_Default::MAX_SELECTED_MESSAGE_COUNT_WITHOUT_REPOST_OR_QUOTE) {
			throw new Domain_Thread_Exception_Message_RepostLimitExceeded("repost message count exceeded");
		}

		// сортируем по индексу сообщения и возвращаем
		return self::_sortMessageMapList($message_map_list);
	}

	/**
	 * Сортируем сообщения по порядку
	 *
	 * @param array $message_map_list
	 *
	 * @return array
	 */
	protected static function _sortMessageMapList(array $message_map_list):array {

		// собираем все сообщения по порядку появление в треде
		$grouped_message_map_list = [];
		foreach ($message_map_list as $message_map) {

			$message_index                            = \CompassApp\Pack\Message\Thread::getThreadMessageIndex($message_map);
			$grouped_message_map_list[$message_index] = $message_map;
		}

		ksort($grouped_message_map_list);
		return array_values($grouped_message_map_list);
	}
}