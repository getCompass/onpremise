<?php

namespace Compass\Premise;

/**
 * Класс-интерфейс для работы с таблицей счётчиков для команд.
 */
class Gateway_Db_PremiseUser_SpaceCounter extends Gateway_Db_PremiseUser_Main {

	protected const _TABLE_KEY = "space_counter";

	/**
	 * Вставить или обновить запись
	 *
	 * @param string $key
	 * @param int    $count
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function insertOrUpdate(string $key, int $count = 0):void {

		$insert = [
			"key"        => $key,
			"count"      => $count,
			"created_at" => time(),
			"updated_at" => time(),
		];

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		ShardingGateway::database(static::_DB_KEY)->insertOrUpdate(static::_TABLE_KEY, $insert);
	}

	/**
	 * Обновить счетчик по ключу
	 *
	 * @param string $key
	 * @param int    $count
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function updateCount(string $key, int $count):void {

		$set = [
			"count"      => $count,
			"updated_at" => time(),
		];

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		ShardingGateway::database(self::_DB_KEY)
			->update("UPDATE `?p` SET ?u WHERE `key` = ?s LIMIT ?i", self::_TABLE_KEY, $set, $key, 1);
	}

	/**
	 * Получить записи по списку ключей
	 *
	 * @param array $key_list
	 *
	 * @return Struct_Db_PremiseUser_SpaceCounter[]
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getList(array $key_list):array {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$list = ShardingGateway::database(self::_DB_KEY)
			->getAll("SELECT * FROM `?p` WHERE `key` IN (?a) LIMIT ?i", self::_TABLE_KEY, $key_list, count($key_list));

		return self::_toStructList($list);
	}

	# region protected

	/**
	 * Формируем список структур из записей базы
	 *
	 * @param array $raw_list
	 *
	 * @return array
	 */
	protected static function _toStructList(array $raw_list):array {

		return array_map(static fn(array $el) => static::_toStruct($el), $raw_list);
	}

	/**
	 * Формируем структуру из записи базы
	 *
	 * @param array $raw
	 *
	 * @return Struct_Db_PremiseUser_SpaceCounter
	 */
	protected static function _toStruct(array $raw):Struct_Db_PremiseUser_SpaceCounter {

		return new Struct_Db_PremiseUser_SpaceCounter(...$raw);
	}

	# endregion protected
}
