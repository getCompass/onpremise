<?php

namespace Compass\Conversation;

/**
 * Класс для работы с сообщениями в блоках
 */
class Domain_Conversation_Entity_Message_Block_Message {

	/**
	 * Возвращает массив со всем сообщениями.
	 */
	public static function getAll(array $block_row):array {

		return $block_row["data"];
	}

	/**
	 * Итерирует все сообщения в блоке.
	 */
	public static function iterate(array $block_row):\Generator {

		foreach ($block_row["data"] as $message) {
			yield $message;
		}
	}

	/**
	 * Есть ли указанное сообщение в блоке.
	 */
	public static function exists(string $message_map, array $block_row):bool {

		return isset($block_row["data"][$message_map]);
	}

	// возвращает сообщение из записи с блоком
	public static function get(string $message_map, array $block_row):array {

		return $block_row["data"][$message_map];
	}

	// устанавливает сообщение в запись с блоком
	public static function set(string $message_map, array $message, array $block_row):array {

		$block_row["data"][$message_map] = $message;
		return $block_row;
	}

	// получаем created_at самого позднего сообщения в блоке
	// тут пошел на хитрость — получаю не самое последнее сообщение, а самое позднее по created_at
	public static function getLastMessageCreatedAt(array $block_row_data):int {

		$last_message_created_at = 0;
		foreach ($block_row_data as $v) {
			$last_message_created_at = max($last_message_created_at, $v["created_at"]);
		}

		return $last_message_created_at;
	}
}