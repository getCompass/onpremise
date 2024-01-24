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
}