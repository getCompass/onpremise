<?php

namespace Compass\Conversation;

/**
 * класс-интерфейс для таблицы dynamic в company_conversation
 */
class Gateway_Db_CompanyConversation_ConversationDynamicLegacy extends Gateway_Db_CompanyConversation_Main {

	protected const _TABLE_KEY = "conversation_dynamic";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// метод для создания записи
	public static function insert(array $insert):void {

		static::_connect(static::_getDbKey())->insert(self::_getTable(), $insert);
	}

	// метод для обновления записи
	public static function set(string $conversation_map, array $set):void {

		static::_connect(static::_getDbKey())->update("UPDATE `?p` SET ?u WHERE conversation_map = ?s LIMIT ?i",
			self::_getTable(), $set, $conversation_map, 1);
	}

	// метод для получения записи
	public static function getOne(string $conversation_map):array {

		$dynamic_row = static::_connect(static::_getDbKey())->getOne("SELECT * FROM `?p` WHERE conversation_map = ?s LIMIT ?i",
			self::_getTable(), $conversation_map, 1);

		if (isset($dynamic_row["conversation_map"])) {

			$dynamic_row["user_clear_info"]         = fromJson($dynamic_row["user_clear_info"]);
			$dynamic_row["conversation_clear_info"] = fromJson($dynamic_row["conversation_clear_info"]);
			$dynamic_row["user_mute_info"]          = fromJson($dynamic_row["user_mute_info"]);
			$dynamic_row["user_file_clear_info"]    = fromJson($dynamic_row["user_file_clear_info"]);
		}

		return $dynamic_row;
	}

	// метод для получения записи на обновление
	public static function getForUpdate(string $conversation_map):array {

		$dynamic_row = static::_connect(static::_getDbKey())->getOne("SELECT * FROM `?p` WHERE conversation_map = ?s LIMIT ?i FOR UPDATE",
			self::_getTable(), $conversation_map, 1);

		if (isset($dynamic_row["conversation_map"])) {

			$dynamic_row["conversation_clear_info"] = fromJson($dynamic_row["conversation_clear_info"]);
			$dynamic_row["user_clear_info"]         = fromJson($dynamic_row["user_clear_info"]);
			$dynamic_row["user_mute_info"]          = fromJson($dynamic_row["user_mute_info"]);
			$dynamic_row["user_file_clear_info"]    = fromJson($dynamic_row["user_file_clear_info"]);
		}

		return $dynamic_row;
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// получаем таблицу
	protected static function _getTable():string {

		return self::_TABLE_KEY;
	}
}