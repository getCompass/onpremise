<?php

namespace Compass\Thread;

/**
 * Класс для работы с сообщениями в блоках
 */
class Domain_Thread_Entity_MessageBlock_Message {

	/**
	 * Итерирует все сообщения в блоке.
	 */
	public static function iterate(array $block_row):\Generator {

		foreach ($block_row["data"] as $message) {
			yield $message;
		}
	}

	// возвращает сообщение из записи с блоком
	public static function get(string $message_map, array $block_row):array {

		return $block_row["data"][$message_map];
	}

}