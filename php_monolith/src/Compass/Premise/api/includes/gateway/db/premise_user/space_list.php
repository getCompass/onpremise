<?php declare(strict_types = 1);

namespace Compass\Premise;

use BaseFrame\Exception\Domain\ParseFatalException;
use cs_RowIsEmpty;

/**
 * Гейтвей для работы с таблтице premise_user.space_list
 */
class Gateway_Db_PremiseUser_SpaceList extends Gateway_Db_PremiseUser_Main {

	protected const _TABLE_KEY = "space_list";

	/**
	 * Получить одну запись
	 *
	 * @throws cs_RowIsEmpty
	 * @throws ParseFatalException
	 */
	public static function getOne(int $user_id, int $space_id):Struct_Db_PremiseUser_Space {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `space_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_key, $user_id, $space_id, 1);
		if (!isset($row["user_id"])) {
			throw new cs_RowIsEmpty();
		}

		return self::_rowToObject($row);
	}

	/**
	 * Вставить новую запись
	 *
	 * @param Struct_Db_PremiseUser_Space $user_space
	 *
	 * @return void
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PremiseUser_Space $user_space):void {

		$insert_array = [
			"user_id"           => $user_space->user_id,
			"space_id"          => $user_space->space_id,
			"role_alias"        => $user_space->role_alias,
			"permissions_alias" => $user_space->permissions_alias,
			"created_at"        => $user_space->created_at,
			"updated_at"        => $user_space->updated_at,
			"extra"             => toJson($user_space->extra),
		];

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		ShardingGateway::database(self::_getDbKey())->insert(self::_getTableKey(), $insert_array);
	}

	/**
	 * Вставить несколько записей
	 *
	 * @param Struct_Db_PremiseUser_Space[] $user_space_list
	 *
	 * @return void
	 * @throws ParseFatalException
	 */
	public static function insertList(array $user_space_list):void {

		$insert_list = [];
		foreach ($user_space_list as $user_space) {

			$insert_list[] = [
				"user_id"           => $user_space->user_id,
				"space_id"          => $user_space->space_id,
				"role_alias"        => $user_space->role_alias,
				"permissions_alias" => $user_space->permissions_alias,
				"created_at"        => $user_space->created_at,
				"updated_at"        => $user_space->updated_at,
				"extra"             => toJson($user_space->extra),
			];
		}

		if (count($insert_list) < 1) {
			return;
		}

		ShardingGateway::database(self::_getDbKey())->insertArray(self::_getTableKey(), $insert_list);
	}

	/**
	 * Обновить запись
	 *
	 * @param int   $user_id
	 * @param int   $space_id
	 * @param array $set
	 *
	 * @return void
	 * @throws ParseFatalException
	 */
	public static function set(int $user_id, int $space_id, array $set):void {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i AND `space_id` = ?i LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->update($query, self::_getTableKey(), $set, $user_id, $space_id, 1);
	}

	/**
	 * Удалить запись
	 *
	 * @param int $user_id
	 * @param int $space_id
	 *
	 * @return void
	 * @throws ParseFatalException
	 */
	public static function delete(int $user_id, int $space_id):void {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "DELETE FROM `?p` WHERE `user_id` = ?i AND `space_id` = ?i LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->update($query, self::_getTableKey(), $user_id, $space_id, 1);
	}

	/**
	 * Удалить записи по user_id
	 *
	 * @param int $user_id
	 *
	 * @return void
	 * @throws ParseFatalException
	 */
	public static function deleteAllByUserId(int $user_id):void {

		$limit = 10000;

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "DELETE FROM `?p` WHERE `user_id` = ?i LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->update($query, self::_getTableKey(), $user_id, $limit);
	}

	/**
	 * Получить записи для одного пользователя
	 *
	 * @param int $user_id
	 * @param int $limit
	 *
	 * @return Struct_Db_PremiseUser_Space[]
	 * @throws ParseFatalException
	 */
	public static function getByUser(int $user_id, int $limit = 10000):array {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query  = "SELECT * FROM `?p` WHERE `user_id` = ?i LIMIT ?i";
		$result = ShardingGateway::database($db_key)->getAll($query, $table_key, $user_id, $limit);

		return array_map(static fn(array $row) => static::_rowToObject($row), $result);
	}

	/**
	 * Получить записи для пользователей из одного пространства
	 *
	 * @param array $user_id_list
	 * @param int   $space_id
	 *
	 * @return Struct_Db_PremiseUser_Space[]
	 * @throws ParseFatalException
	 */
	public static function getByUserListAndSpace(array $user_id_list, int $space_id):array {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query  = "SELECT * FROM `?p` WHERE `user_id` IN (?a) AND `space_id` = ?i LIMIT ?i";
		$result = ShardingGateway::database($db_key)->getAll($query, $table_key, $user_id_list, $space_id, count($user_id_list));

		return array_map(static fn(array $row) => static::_rowToObject($row), $result);
	}

	/**
	 * Создать структуру из записи БД
	 *
	 * @param array $row
	 *
	 * @return Struct_Db_PremiseUser_Space
	 */
	protected static function _rowToObject(array $row):Struct_Db_PremiseUser_Space {

		return new Struct_Db_PremiseUser_Space(
			$row["user_id"],
			$row["space_id"],
			$row["role_alias"],
			$row["permissions_alias"],
			$row["created_at"],
			$row["updated_at"],
			fromJson($row["extra"]),
		);
	}

	/**
	 * Получить таблицу
	 *
	 * @return string
	 */
	protected static function _getTableKey():string {

		return self::_TABLE_KEY;
	}
}
