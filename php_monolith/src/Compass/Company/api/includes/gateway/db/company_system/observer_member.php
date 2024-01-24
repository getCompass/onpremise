<?php

namespace Compass\Company;

/**
 * Класс-интерфейс для базы данных company_system.observer_member
 */
class Gateway_Db_CompanySystem_ObserverMember extends Gateway_Db_CompanySystem_Main {

	protected const _TABLE_KEY = "observer_member";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// получаем строку пользователя
	public static function get(int $user_id):array {

		// получаем ключ базы данных
		$db_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::getTableName();

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_name, $user_id, 1);

		if (isset($row["user_id"])) {
			$row["data"] = fromJson($row["data"]);
		}

		return $row;
	}

	/**
	 * Получить всех пользователей
	 */
	public static function getAll(int $limit, int $offset):array {

		// получаем ключ базы данных
		$db_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::getTableName();

		$query = "SELECT * FROM `?p` WHERE TRUE LIMIT ?i OFFSET ?i";
		$list  = ShardingGateway::database($db_key)->getAll($query, $table_name, $limit, $offset);

		foreach ($list as $k => $row) {

			$row["data"] = fromJson($row["data"]);
			$list[$k]    = $row;
		}

		return $list;
	}

	// метод для вставки/обновления записи
	public static function insertOrUpdate(array $insert):int {

		// получаем ключ базы данных
		$db_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::getTableName();

		// осуществляем запрос
		return ShardingGateway::database($db_key)->insertOrUpdate($table_name, $insert);
	}

	// метод для обновления записи
	public static function set(int $user_id, array $set):void {

		// получаем ключ базы данных
		$db_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i LIMIT ?i";
		ShardingGateway::database($db_key)->update($query, $table_name, $set, $user_id, 1);
	}

	// метод для обновления несколько записей
	public static function setList(array $user_id_list, array $set):void {

		// получаем ключ базы данных
		$db_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "UPDATE `?p` SET ?u WHERE user_id IN (?a) LIMIT ?i";
		ShardingGateway::database($db_key)->update($query, $table_name, $set, $user_id_list, count($user_id_list));
	}

	// удаляем пользователя
	public static function delete(int $user_id):void {

		// получаем ключ базы данных
		$db_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::getTableName();

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "DELETE FROM `?p` WHERE `user_id` = ?i LIMIT ?i";
		ShardingGateway::database($db_key)->delete($query, $table_name, $user_id, 1);
	}

	// -------------------------------------------------------
	// UTILS
	// -------------------------------------------------------

	// функция возвращает название таблицы
	public static function getTableName():string {

		return self::_TABLE_KEY;
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------
}