<?php

namespace Compass\Company;

use BaseFrame\Server\ServerProvider;
use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс-интерфейс для таблицы company_data.hiring_conversation_preset
 */
class Gateway_Db_CompanyData_HiringConversationPreset extends Gateway_Db_CompanyData_Main {

	protected const _TABLE_KEY = "hiring_conversation_preset";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для создания записи
	 *
	 * @throws \queryException
	 */
	public static function insert(int $status, int $creator_user_id, string $title, array $conversation_list):string {

		$insert = [
			"status"            => $status,
			"creator_user_id"   => $creator_user_id,
			"created_at"        => time(),
			"updated_at"        => 0,
			"title"             => $title,
			"conversation_list" => $conversation_list,
		];

		return ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_KEY, $insert);
	}

	/**
	 * метод для обновления записи
	 *
	 * @throws \parseException
	 */
	public static function set(int $hiring_conversation_preset_id, array $set):int {

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_CompanyData_HiringConversationPreset::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "UPDATE `?p` SET ?u WHERE hiring_conversation_preset_id = ?i LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_KEY, $set, $hiring_conversation_preset_id, 1);
	}

	/**
	 * метод для получения записи
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(int $hiring_conversation_preset_id):Struct_Db_CompanyData_HiringConversationPreset {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE `hiring_conversation_preset_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $hiring_conversation_preset_id, 1);

		if (!isset($row["hiring_conversation_preset_id"])) {
			throw new \cs_RowIsEmpty();
		}
		return self::_rowToObject($row);
	}

	/**
	 * Получаем пресет конкретного пользователя
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOneForUser(int $user_id, int $hiring_conversation_preset_id):Struct_Db_CompanyData_HiringConversationPreset {

		// формируем и осуществляем запрос
		// проверил на EXPLAIN (key=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `creator_user_id` = ?i AND `hiring_conversation_preset_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $user_id, $hiring_conversation_preset_id, 1);

		if (!isset($row["hiring_conversation_preset_id"])) {
			throw new \cs_RowIsEmpty();
		}
		return self::_rowToObject($row);
	}

	/**
	 * Получаем пресет конкретного пользователя со статусом
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOneForUserWithStatus(int $user_id, int $hiring_conversation_preset_id, int $status):Struct_Db_CompanyData_HiringConversationPreset {

		// формируем и осуществляем запрос
		// проверил на EXPLAIN (key: PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `creator_user_id` = ?i AND `status` = ?i AND `hiring_conversation_preset_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $user_id, $status, $hiring_conversation_preset_id, 1);

		if (!isset($row["hiring_conversation_preset_id"])) {
			throw new \cs_RowIsEmpty();
		}
		return self::_rowToObject($row);
	}

	/**
	 * Получаем список пресетов конкретного пользователя со статусом
	 *
	 * @return Struct_Db_CompanyData_HiringConversationPreset[]
	 */
	public static function getListForUserWithStatus(int $user_id, int $status, int $limit):array {

		// формируем и осуществляем запрос
		// проверил на EXPLAIN (key: user_status)
		$query    = "SELECT * FROM `?p` FORCE INDEX(`user_status`)  WHERE `creator_user_id` = ?i AND `status` = ?i LIMIT ?i";
		$row_list = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $user_id, $status, $limit);

		$result = [];
		foreach ($row_list as $row) {
			$result[] = self::_rowToObject($row);
		}

		return $result;
	}

	/**
	 * Получаем список пресетов конкретного пользователя со статусом по списку id
	 *
	 * @return Struct_Db_CompanyData_HiringConversationPreset[]
	 */
	public static function getListForUserWithStatusById(int $user_id, int $status, array $preset_id_list):array {

		// формируем и осуществляем запрос
		// проверил на EXPLAIN (key: user_status)
		$query    = "SELECT * FROM `?p` WHERE `creator_user_id` = ?i AND `status` = ?i AND `hiring_conversation_preset_id` IN (?a) LIMIT ?i";
		$row_list = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $user_id, $status, $preset_id_list, 1000);

		$result = [];
		foreach ($row_list as $row) {
			$result[] = self::_rowToObject($row);
		}

		return $result;
	}

	/**
	 * Делаем запрос с блокировкой
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getForUpdateWithStatus(int $hiring_conversation_preset_id, int $user_id, int $status):Struct_Db_CompanyData_HiringConversationPreset {

		// формируем и осуществляем запрос
		// проверил на EXPLAIN (key: PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `hiring_conversation_preset_id` = ?i AND `creator_user_id` = ?i AND `status` = ?i LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $hiring_conversation_preset_id, $user_id, $status, 1);

		if (!isset($row["hiring_conversation_preset_id"])) {
			throw new \cs_RowIsEmpty();
		}
		return self::_rowToObject($row);
	}

	/**
	 * Получаем кол-во по id создателя и статус
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getCount(int $creator_user_id, int $status):int {

		// формируем и осуществляем запрос
		// проверил на EXPLAIN (key: user_status)
		$query = "SELECT COUNT(*) FROM `?p` WHERE `creator_user_id` = ?i AND `status` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $creator_user_id, $status, 1);

		if (!isset($row["COUNT(*)"])) {
			throw new \cs_RowIsEmpty();
		}
		return $row["COUNT(*)"];
	}

	/**
	 * Удаляем все записи
	 */
	public static function deleteAll():void {

		if (!ServerProvider::isTest()) {
			throw new ParseFatalException("delete action not allowed");
		}

		// формируем и осуществляем запрос
		$query = "DELETE FROM `?p` WHERE ?i = ?i LIMIT ?i";
		ShardingGateway::database(self::_DB_KEY)->delete($query, self::_TABLE_KEY, 1, 1, 10000);
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	/**
	 * Создаем структуру из строки бд
	 */
	protected static function _rowToObject(array $row):Struct_Db_CompanyData_HiringConversationPreset {

		return new Struct_Db_CompanyData_HiringConversationPreset(
			$row["hiring_conversation_preset_id"],
			$row["status"],
			$row["creator_user_id"],
			$row["created_at"],
			$row["updated_at"],
			$row["title"],
			fromJson($row["conversation_list"]),
		);
	}
}