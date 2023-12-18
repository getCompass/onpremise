<?php

namespace Compass\FileBalancer;

/**
 * Класс для работы с таблицей company_system.auto_increment и получения next_id для сущности по ее key
 */
class Type_Autoincrement_Company {

	public const FILE_META_ID = "company_file_meta_id";    // ключ для meta_id пивота

	protected const _DB_KEY    = "company_system";
	protected const _TABLE_KEY = "auto_increment";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Метод для инкремента и получения идентификатора новой сущности
	 *
	 * @throws queryException
	 * @throws returnException
	 */
	public static function getNextId(string $key, int $inc = 1):int {

		// начинаем транзакцию на dpc_main
		ShardingGateway::database(self::_DB_KEY)->beginTransaction();

		$result = self::getNextIdWithoutLock($key, $inc);

		// закрываем транзакцию на dpc_main
		if (!ShardingGateway::database(self::_DB_KEY)->commit()) {
			throw new returnException("transaction was failed in method: " . __METHOD__);
		}

		return $result;
	}

	/**
	 * Метод для инкремента и получения идентификатора новой сущности.
	 * Работает только в транзакции.
	 *
	 * @throws queryException
	 */
	public static function getNextIdWithoutLock(string $key, int $inc = 1):int {

		if (!ShardingGateway::database(self::_DB_KEY)->inTransaction()) {
			throw new queryException("this function need to be wrapped into a transaction");
		}

		// обновляем запись в базе
		$row_count = self::_updateRow($key, $inc);

		// если не обновилось ни одной записи - создаем пустую
		// (такое произойдет всего один раз)
		if ($row_count != 1) {
			self::_createRow($key, $inc);
		}

		// получаем значение
		$query  = "SELECT * FROM `?p` WHERE `key` = ?s LIMIT ?i";
		$result = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $key, 1);

		return $result["value"];
	}

	/**
	 * Метод для инкремента по ключу
	 *
	 * @throws queryException
	 * @throws returnException
	 */
	public static function inc(string $key, int $inc = 1):void {

		// начинаем транзакцию на dpc_main
		ShardingGateway::database(self::_DB_KEY)->beginTransaction();

		// обновляем запись в базе
		$row_count = self::_updateRow($key, $inc);

		// если не обновилось ни одной записи - создаем пустую
		// (такое произойдет всего один раз)
		if ($row_count != 1) {
			self::_createRow($key, $inc);
		}

		// закрываем транзакцию на dpc_main
		if (!ShardingGateway::database(self::_DB_KEY)->commit()) {
			throw new returnException("transaction was failed in method: " . __METHOD__);
		}
	}

	/**
	 * Метод для создания записи в таблице auto_increment
	 *
	 * @throws queryException
	 */
	protected static function _createRow(string $key, int $inc):void {

		ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_KEY, [
			"key"   => $key,
			"value" => 0,
		]);

		// обновляем запись в базе
		self::_updateRow($key, $inc);
	}

	/**
	 * Метод для обновления записи в таблице auto_increment
	 *
	 */
	protected static function _updateRow(string $key, int $inc = 1):int {

		// обновляем
		$set   = [
			"value" => "value + " . $inc,
		];
		$query = "UPDATE `?p` SET ?u WHERE `key` = ?s LIMIT ?i";

		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_KEY, $set, $key, 1);
	}
}