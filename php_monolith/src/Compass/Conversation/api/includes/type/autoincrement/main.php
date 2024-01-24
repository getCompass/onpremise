<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * класс для работы с таблицей cloud_main_conversation.auto_increment и получения next_id для сущности по ее key
 */
class Type_Autoincrement_Main {

	// название базы и таблицы
	protected const _DB_KEY    = "company_system";
	protected const _TABLE_KEY = "auto_increment";

	public const CONVERSATION_META = CURRENT_MODULE . "_conversation_meta";
	public const INVITE            = CURRENT_MODULE . "_invite";

	// -------------------------------------------------------
	// PUBLIC
	// -------------------------------------------------------

	// метод для инкремента и получения идентификатора новой сущности
	public static function getNextId(string $key):int {

		// проверяем вне транзакции, что запись есть и вставляем, если не нашли
		// нужно так, чтобы не вносить неопределенность в транзакцию
		// FOR UPDATE не залочит запись, так как нечего лочит, вставит ее и начнет обновлять, и вторая транзакция тоже попытается ее вставить и обновить
		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE `key` = ?s LIMIT ?i";
		$row   = static::_connect()->getOne($query, self::_TABLE_KEY, $key, 1);

		if (!isset($row["key"])) {
			self::_insertRow($key);
		}

		// начинаем транзакцию
		static::_connect()->beginTransaction();

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE `key` = ?s LIMIT ?i FOR UPDATE";
		$row   = static::_connect()->getOne($query, self::_TABLE_KEY, $key, 1);

		// обновляем запись в базе
		self::_incRow($key);

		// закрываем транзакцию
		if (!static::_connect()->commit()) {
			throw new ReturnFatalException("transaction was failed in method: " . __METHOD__);
		}

		return $row["value"] + 1;
	}

	// метод для создания записи в таблице auto_increment
	protected static function _insertRow(string $key):void {

		static::_connect()->insert(self::_TABLE_KEY, [
			"key"   => $key,
			"value" => 0,
		]);
	}

	// метод для обновления записи в таблице auto_increment
	protected static function _incRow(string $key):int {

		// обновляем
		$set   = [
			"value" => "value + 1",
		];
		$query = "UPDATE `?p` SET ?u WHERE `key` = ?s LIMIT ?i";

		return static::_connect()->update($query, self::_TABLE_KEY, $set, $key, 1);
	}

	/**
	 * Создает подключение к базе данных
	 */
	protected static function _connect():\myPDObasic {

		return ShardingGateway::database(static::_DB_KEY);
	}
}