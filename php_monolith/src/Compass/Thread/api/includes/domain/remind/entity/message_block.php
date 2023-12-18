<?php

namespace Compass\Thread;

/**
 * класс для работы с message_block и Напоминания
 */
class Domain_Remind_Entity_MessageBlock {

	// версия упаковщика
	protected const _MESSAGE_BLOCK_ITEM_VERSION = 1;

	// схема message_block_remind_item
	protected const _MESSAGE_BLOCK_ITEM_SCHEMA = [

		1 => [
			"remind_id"       => 0,
			"remind_at"       => 0,
			"creator_user_id" => 0,
			"comment"         => "",
		],
	];

	/**
	 * инициализируем message_block_remind_item
	 */
	public static function initItem(int $remind_id, int $remind_at, int $creator_user_id, string $comment = ""):array {

		$message_block_remind_item["data"] = self::_MESSAGE_BLOCK_ITEM_SCHEMA[self::_MESSAGE_BLOCK_ITEM_VERSION];

		$message_block_remind_item["data"]["remind_id"]       = $remind_id;
		$message_block_remind_item["data"]["remind_at"]       = $remind_at;
		$message_block_remind_item["data"]["creator_user_id"] = $creator_user_id;
		$message_block_remind_item["data"]["comment"]         = $comment;

		$message_block_remind_item["version"] = self::_MESSAGE_BLOCK_ITEM_VERSION;

		return $message_block_remind_item;
	}

	/**
	 * получаем remind_id
	 */
	public static function getRemindId(array $message_block_remind_item):int {

		$message_block_remind_item = self::_getData($message_block_remind_item);

		return (int) $message_block_remind_item["data"]["remind_id"];
	}

	/**
	 * получаем creator_user_id
	 */
	public static function getCreatorUserId(array $message_block_remind_item):int {

		$message_block_remind_item = self::_getData($message_block_remind_item);

		return (int) $message_block_remind_item["data"]["creator_user_id"];
	}

	/**
	 * получаем remind_at
	 */
	public static function getRemindAt(array $message_block_remind_item):int {

		$message_block_remind_item = self::_getData($message_block_remind_item);

		return (int) $message_block_remind_item["data"]["remind_at"];
	}

	/**
	 * получаем comment
	 */
	public static function getComment(array $message_block_remind_item):string {

		$message_block_remind_item = self::_getData($message_block_remind_item);

		return (string) $message_block_remind_item["data"]["comment"];
	}

	/**
	 * получаем флаг является ли создателем Напоминания пользователь
	 */
	public static function isCreator(int $user_id, array $message_block_remind_item):bool {

		return $user_id == self::getCreatorUserId($message_block_remind_item);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * получить актуальную структуру для extra
	 */
	protected static function _getData(array $message_block_remind_item):array {

		// если версия не совпадает - дополняем её до текущей
		if ($message_block_remind_item["version"] != self::_MESSAGE_BLOCK_ITEM_VERSION) {

			$message_block_remind_item["extra"]   = array_merge(
				self::_MESSAGE_BLOCK_ITEM_SCHEMA[self::_MESSAGE_BLOCK_ITEM_VERSION], $message_block_remind_item["data"]
			);
			$message_block_remind_item["version"] = self::_MESSAGE_BLOCK_ITEM_VERSION;
		}

		return $message_block_remind_item;
	}
}