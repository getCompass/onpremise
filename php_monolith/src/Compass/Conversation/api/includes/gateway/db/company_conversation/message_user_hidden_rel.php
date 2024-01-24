<?php

namespace Compass\Conversation;

/**
 * класс-интерфейс для таблицы message_user_hidden_rel в company_conversation
 */
class Gateway_Db_CompanyConversation_MessageUserHiddenRel extends Gateway_Db_CompanyConversation_Main {

	protected const _TABLE_KEY = "message_user_hidden_rel";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// метод для создания записи
	public static function insertArray(array $insert_array):void {

		$sharding_key = self::_getDbKey();
		static::_connect($sharding_key)->insertArray(self::_getTable(), $insert_array);
	}

	// метод для получения списка файлов

	/**
	 *
	 * @return string[]
	 */
	public static function getMessageMapList(int $user_id, array $message_map_list):array {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$sharding_key = self::_getDbKey();
		$query        = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `message_map` IN (?a) LIMIT ?i";
		$list         = static::_connect($sharding_key)->getAll($query, self::_getTable(), $user_id, $message_map_list, count($message_map_list));

		$output = [];

		// форматируем строки в массив
		foreach ($list as $v) {
			$output[] = $v["message_map"];
		}

		return $output;
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// получаем таблицу
	protected static function _getTable():string {

		return self::_TABLE_KEY;
	}
}