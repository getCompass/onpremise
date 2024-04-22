<?php declare(strict_types = 1);

namespace Compass\Premise;

use BaseFrame\Exception\Domain\ParseFatalException;
use cs_RowIsEmpty;

/**
 * Гейтвей для работы с таблицей premise_user.user_list
 */
class Gateway_Db_PremiseUser_UserList extends Gateway_Db_PremiseUser_Main {

	protected const _TABLE_KEY = "user_list";

	/**
	 * Получить одну запись
	 *
	 * @param int $user_id
	 *
	 * @return Struct_Db_PremiseUser_User
	 * @throws ParseFatalException
	 * @throws cs_RowIsEmpty
	 */
	public static function getOne(int $user_id):Struct_Db_PremiseUser_User {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_key, $user_id, 1);

		if (!isset($row["user_id"])) {
			throw new cs_RowIsEmpty();
		}

		return self::_rowToObject($row);
	}

	/**
	 * Получить несколько записей
	 *
	 * @param array $user_id_list
	 *
	 * @return Struct_Db_PremiseUser_User[]
	 * @throws ParseFatalException
	 */
	public static function getList(array $user_id_list):array {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$list = ShardingGateway::database($db_key)
			->getAll("SELECT * FROM `?p` WHERE `user_id` IN (?a) LIMIT ?i", $table_key, $user_id_list, count($user_id_list));

		$prepare_list = [];
		foreach ($list as $row) {
			$prepare_list[$row["user_id"]] = self::_rowToObject($row);
		}

		return $prepare_list;
	}

	/**
	 * Вставить новую запись
	 *
	 * @param Struct_Db_PremiseUser_User $user
	 *
	 * @return void
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PremiseUser_User $user):void {

		$insert = [
			"user_id"                 => $user->user_id,
			"npc_type_alias"          => $user->npc_type_alias,
			"space_status"            => $user->space_status,
			"has_premise_permissions" => $user->has_premise_permissions,
			"premise_permissions"     => $user->premise_permissions,
			"created_at"              => $user->created_at,
			"updated_at"              => $user->updated_at,
			"external_sso_id"         => $user->external_sso_id,
			"external_other1_id"      => $user->external_other1_id,
			"external_other2_id"      => $user->external_other1_id,
			"external_data"           => toJson($user->external_data),
			"extra"                   => toJson($user->extra),
		];

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		ShardingGateway::database(self::_getDbKey())->insert(self::_getTableKey(), $insert);
	}

	/**
	 * Вставить несколько записей.
	 *
	 * @param Struct_Db_PremiseUser_User[] $user_list
	 *
	 * @throws ParseFatalException
	 */
	public static function insertList(array $user_list):void {

		$insert_list = [];
		foreach ($user_list as $user) {

			$insert_list[] = [
				"user_id"                 => $user->user_id,
				"npc_type_alias"          => $user->npc_type_alias,
				"space_status"            => $user->space_status,
				"has_premise_permissions" => $user->has_premise_permissions,
				"premise_permissions"     => $user->premise_permissions,
				"created_at"              => $user->created_at,
				"updated_at"              => $user->updated_at,
				"external_sso_id"         => $user->external_sso_id,
				"external_other1_id"      => $user->external_other1_id,
				"external_other2_id"      => $user->external_other1_id,
				"external_data"           => toJson($user->external_data),
				"extra"                   => toJson($user->extra),
			];
		}

		if (count($insert_list) < 1) {
			return;
		}

		ShardingGateway::database(self::_getDbKey())->insertArray(self::_getTableKey(), $insert_list);
	}

	/**
	 * Обновляем запись
	 *
	 * @param int   $user_id
	 * @param array $set
	 *
	 * @return void
	 * @throws ParseFatalException
	 */
	public static function set(int $user_id, array $set):void {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->update($query, self::_getTableKey(), $set, $user_id, 1);
	}

	/**
	 * Удаляем запись
	 *
	 * @param string $user_id
	 *
	 * @return void
	 * @throws ParseFatalException
	 */
	public static function delete(string $user_id):void {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "DELETE FROM `?p` WHERE `user_id` = ?i LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->update($query, self::_getTableKey(), $user_id, 1);
	}

	/**
	 * Получает количество записей по npc_type_alias и space_status
	 *
	 * @throws ParseFatalException
	 */
	public static function getCountByNpcAndSpaceStatus(int $npc_type_alias, int $space_status):int {

		// запрос проверен на EXPLAIN (INDEX=`npc_type_alias.space_status`) Котов В.В. 28.03.2024
		$query = "SELECT COUNT(*) AS `count` FROM `?p` WHERE `npc_type_alias` = ?i AND `space_status` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_getTableKey(), $npc_type_alias, $space_status, 1);

		return $row["count"] ?? 0;
	}

	/**
	 * Получаем записи по npc_type и имеющие права во флаге has_premise_permissions
	 *
	 * @param int  $npc_type_alias
	 * @param bool $is_assoc
	 *
	 * @return Struct_Db_PremiseUser_User[]
	 * @throws ParseFatalException
	 */
	public static function getByNpcTypeAndHasPermissions(int $npc_type_alias, bool $is_assoc = false):array {

		// запрос проверен на EXPLAIN (INDEX=`npc_type_alias.has_premise_permissions `) Котов В.В. 18.03.2024
		$query = "SELECT * FROM `?p` WHERE `npc_type_alias` = ?i AND `has_premise_permissions` = ?i LIMIT ?i";
		$list  = ShardingGateway::database(self::_getDbKey())
			->getAll($query, self::_getTableKey(), $npc_type_alias, 1, 10000);

		$structure_list = [];

		if ($is_assoc) {

			foreach ($list as $row) {
				$structure_list[$row["user_id"]] = self::_rowToObject($row);
			}
		} else {

			foreach ($list as $row) {
				$structure_list[] = self::_rowToObject($row);
			}
		}

		return $structure_list;
	}

	/**
	 * Получить все записи
	 *
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return Struct_Db_PremiseUser_User[]
	 * @throws ParseFatalException
	 */
	public static function getAll(int $limit = 10000, int $offset = 0):array {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		$list = ShardingGateway::database($db_key)
			->getAll("SELECT * FROM `?p` WHERE TRUE LIMIT ?i OFFSET ?i", $table_key, $limit, $offset);

		$prepare_list = [];
		foreach ($list as $row) {
			$prepare_list[$row["user_id"]] = self::_rowToObject($row);
		}

		return $prepare_list;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Создаем структуру из строки БД
	 *
	 * @param array $row
	 *
	 * @return Struct_Db_PremiseUser_User
	 */
	protected static function _rowToObject(array $row):Struct_Db_PremiseUser_User {

		return new Struct_Db_PremiseUser_User(
			$row["user_id"],
			$row["npc_type_alias"],
			$row["space_status"],
			$row["has_premise_permissions"],
			$row["premise_permissions"],
			$row["created_at"],
			$row["updated_at"],
			$row["external_sso_id"],
			$row["external_other1_id"],
			$row["external_other2_id"],
			fromjson($row["external_data"]),
			fromJson($row["extra"])
		);
	}

	/**
	 * Получает таблицу
	 */
	protected static function _getTableKey():string {

		return self::_TABLE_KEY;
	}
}
