<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\QueryFatalException;

/**
 * Класс для работы с таблицей pivot_system.auto_increment и получения next_id для сущности по ее key
 */
class Type_Autoincrement_Pivot {

	public const JITSI_INCREMENTAL_CONFERENCE_ID_KEY = "jitsi_incremental_conference_id";          // ключ для user_id

	// дефолт значения для ключей автоинкремента
	protected const _DEFAULT_VALUE_FOR_CREATE_ROW = [
		self::JITSI_INCREMENTAL_CONFERENCE_ID_KEY => BEGIN_INCREMENTAL_CONFERENCE_ID,
	];

	protected const _DB_KEY    = "pivot_system";
	protected const _TABLE_KEY = "auto_increment";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Метод для инкремента и получения идентификатора новой сущности
	 *
	 * @param string $key
	 * @param int    $inc
	 *
	 * @return int
	 * @throws QueryFatalException
	 * @throws ReturnFatalException
	 * @throws \queryException
	 */
	public static function getNextId(string $key, int $inc = 1):int {

		// начинаем транзакцию на dpc_main
		ShardingGateway::database(self::_DB_KEY)->beginTransaction();

		$result = self::getNextIdWithoutLock($key, $inc);

		// закрываем транзакцию на dpc_main
		if (!ShardingGateway::database(self::_DB_KEY)->commit()) {
			throw new ReturnFatalException("transaction was failed in method: " . __METHOD__);
		}

		return $result;
	}

	/**
	 * Метод для инкремента и получения идентификатора новой сущности.
	 * Работает только в транзакции.
	 *
	 * @param string $key
	 * @param int    $inc
	 *
	 * @return int
	 * @throws QueryFatalException
	 * @throws \queryException
	 */
	public static function getNextIdWithoutLock(string $key, int $inc = 1):int {

		if (!ShardingGateway::database(self::_DB_KEY)->inTransaction()) {
			throw new QueryFatalException("this function need to be wrapped into a transaction");
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
	 * @param string $key
	 * @param int    $inc
	 *
	 * @throws ReturnFatalException
	 * @throws \queryException
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
			throw new ReturnFatalException("transaction was failed in method: " . __METHOD__);
		}
	}

	/**
	 * Метод для создания записи в таблице auto_increment
	 *
	 * @throws \queryException
	 */
	protected static function _createRow(string $key, int $inc):void {

		$value = self::_DEFAULT_VALUE_FOR_CREATE_ROW[$key] ?? 0;

		ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_KEY, [
			"key"   => $key,
			"value" => $value,
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

	/**
	 * Метод для проверки наличия записи
	 * Используется только в CI
	 *
	 * @param string $key
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function assertRowExist(string $key):void {

		if (!isTestServer()) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("only on test servers");
		}

		// получаем значение
		$query  = "SELECT * FROM `?p` WHERE `key` = ?s LIMIT ?i";
		$result = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $key, 1);
		if (!isset($result["key"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("row not found");
		}
	}
}