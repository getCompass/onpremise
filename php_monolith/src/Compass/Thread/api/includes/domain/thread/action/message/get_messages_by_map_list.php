<?php

namespace Compass\Thread;

/**
 * Действие получения списка сообщение по указанному набору идентификаторов.
 */
class Domain_Thread_Action_Message_GetMessagesByMapList {

	/**
	 * Возвращает список всех совпадений типа «Сообщение диалога».
	 */
	public static function run(array $thread_map_list):array {

		// загружаем все необходимые блоки и выбираем из них подходящие сообщения
		return static::_filterSpecifiedMessages(static::_loadMessageBlocks($thread_map_list), $thread_map_list);
	}

	/**
	 * Загружаем все блоки, которые содержат переданные сообщения.
	 * Не обращаем внимания на то, принадлежит ли они одному треду или нет.
	 */
	protected static function _loadMessageBlocks(array $message_map_list):array {

		$block_list_by_thread_map = [];

		foreach ($message_map_list as $message_map) {

			// получаем map треда и идентификатор блока сообщения
			$thread_map = \CompassApp\Pack\Message\Thread::getThreadMap($message_map);
			$block_id   = \CompassApp\Pack\Message\Thread::getBlockId($message_map);

			$block_list_by_thread_map[$thread_map][] = $block_id;
		}

		if (count($block_list_by_thread_map) === 0) {
			return [];
		}

		foreach ($block_list_by_thread_map as $index => $block_id_list) {
			$block_list_by_thread_map[$index] = array_values(array_unique($block_id_list));
		}

		return Gateway_Db_CompanyThread_MessageBlock::getSpecifiedList($block_list_by_thread_map);
	}

	/**
	 * Выполняет поиск запрошенных сообщений в списке блоков.
	 */
	protected static function _filterSpecifiedMessages(array $message_block_list, array $message_map_list):array {

		$flipped_key_list = array_flip($message_map_list);
		$output           = [];

		// пробегаем по всем блокам
		foreach ($message_block_list as $message_block) {

			// в каждом блоке ищем сообщения из указанного списка
			foreach (Domain_Thread_Entity_MessageBlock_Message::iterate($message_block) as $message) {

				if (!isset($flipped_key_list[$message["message_map"]])) {
					continue;
				}

				$output[] = $message;
			}
		}

		return $output;
	}
}