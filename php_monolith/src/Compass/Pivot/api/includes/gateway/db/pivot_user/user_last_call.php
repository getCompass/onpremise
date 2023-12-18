<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Server\ServerProvider;

/**
 * класс-интерфейс для таблицы pivot_user_{10m}.user_last_call{n}
 */
class Gateway_Db_PivotUser_UserLastCall extends Gateway_Db_PivotUser_Main {

	protected const _TABLE_KEY = "user_last_call";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * создаем (или обновляем сущетсвующие) записи для пользователей и сразу же помечаем их телефонную линию занятой
	 */
	public static function markCallLineAsBusyForUserList(array $user_id_list, string $call_key, int $company_id):void {

		$grouped_user_id_list = [];

		// группируем пользователей по принадлежности к базе
		$db_grouped_user_id_list = self::_groupedUserIdListByDbKey($user_id_list);

		// группируем пользователей по принадлежности к таблице
		foreach ($db_grouped_user_id_list as $k => $user_list) {
			$grouped_user_id_list[$k] = self::_groupedUserIdListByTableName($user_list);
		}

		foreach ($grouped_user_id_list as $db_key => $db_user_list) {

			foreach ($db_user_list as $table_name => $user_list) {

				// создаем записи или обновляем существующие сразу для нескольких пользователей
				$insert_array = self::_makeInsertArray($user_list, $call_key, $company_id);
				$update       = [
					"is_finished" => 0,
					"call_key"    => $call_key,
					"company_id"  => $company_id,
					"updated_at"  => time(),
				];
				ShardingGateway::database($db_key)->insertArrayOrUpdate($table_name, $insert_array, $update);
			}
		}
	}

	/**
	 * собираем массив для insert_array
	 *
	 * @return array
	 */
	protected static function _makeInsertArray(array $user_list, string $call_key, int $company_id):array {

		$output = [];
		foreach ($user_list as $user_id) {

			$output[] = [
				"user_id"     => $user_id,
				"company_id"  => $company_id,
				"call_key"    => $call_key,
				"is_finished" => 0,
				"type"        => 0,
				"created_at"  => time(),
				"updated_at"  => 0,
				"extra"       => [],
			];
		}

		return $output;
	}

	// метод для создания записи
	public static function insertOrUpdate(int $user_id, array $insert):void {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// осуществляем запрос
		ShardingGateway::database($shard_key)->insertOrUpdate($table_name, $insert);
	}

	// метод для получения записи пользователя
	public static function getOne(int $user_id):Struct_Db_PivotUser_UserLastCall {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// формируем и осуществляем запрос
		// EXPLAIN: key=PRIMARY
		$query = "SELECT * FROM `?p` WHERE user_id=?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, 1);

		if (!isset($row["user_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToObject($row);
	}

	/**
	 * получаем активные звонки пользователей
	 *
	 * @return Struct_Db_PivotUser_UserLastCall[]
	 */
	public static function getListActive(array $user_id_list):array {

		$grouped_user_id_list = [];
		$is_finished          = 0;

		// группируем пользователей по принадлежности к базе
		$db_grouped_user_id_list = self::_groupedUserIdListByDbKey($user_id_list);

		// группируем пользователей по принадлежности к таблице
		foreach ($db_grouped_user_id_list as $k => $user_list) {
			$grouped_user_id_list[$k] = self::_groupedUserIdListByTableName($user_list);
		}

		$grouped_query_list = [];
		foreach ($grouped_user_id_list as $db_key => $db_user_list) {

			foreach ($db_user_list as $table_name => $user_list) {

				// формируем и осуществляем запрос
				// EXPLAIN: key=PRIMARY
				$query                = "SELECT * FROM `?p` WHERE user_id IN (?a) AND is_finished = ?i LIMIT ?i";
				$grouped_query_list[] = ShardingGateway::database($db_key)->getAll($query, $table_name, $user_list, $is_finished, count($user_id_list));
			}
		}

		// собираем массив объектов
		$output_list = [];
		foreach ($grouped_query_list as $query_list) {

			foreach ($query_list as $row) {
				$output_list[] = self::_rowToObject($row);
			}
		}

		return $output_list;
	}

	/**
	 * получаем записи о последнем звонке пользователей
	 *
	 * @return Struct_Db_PivotUser_UserLastCall[]
	 */
	public static function getList(array $user_id_list):array {

		$grouped_user_id_list = [];

		// группируем пользователей по принадлежности к базе
		$db_grouped_user_id_list = self::_groupedUserIdListByDbKey($user_id_list);

		// группируем пользователей по принадлежности к таблице
		foreach ($db_grouped_user_id_list as $k => $user_list) {
			$grouped_user_id_list[$k] = self::_groupedUserIdListByTableName($user_list);
		}

		$result_list = [];
		foreach ($grouped_user_id_list as $db_key => $db_user_list) {

			foreach ($db_user_list as $table_name => $user_list) {

				// формируем и осуществляем запрос
				// EXPLAIN: key=PRIMARY
				$query         = "SELECT * FROM `?p` WHERE user_id IN (?a) LIMIT ?i";
				$result_list[] = ShardingGateway::database($db_key)->getAll($query, $table_name, $user_list, count($user_id_list));
			}
		}

		// собираем массив объектов
		$output_list = [];
		foreach ($result_list as $list) {

			foreach ($list as $row) {
				$output_list[] = self::_rowToObject($row);
			}
		}

		return $output_list;
	}

	// метод для получения всех активных звонков (используется только в тестовой среде)
	public static function getAllActive(int $company_id):array {

		if (!ServerProvider::isTest()) {
			throw new ParseFatalException(__METHOD__ . ": method allow only for test-server environment");
		}

		$shard_key  = self::_getDbKey(1);
		$table_name = self::_getTableKey(1);

		// формируем и осуществляем запрос
		// EXPLAIN тут не нужен, как и сам индекс так как функция используется только в тестовых средах
		$query = "SELECT * FROM `?p` WHERE is_finished = ?i AND `company_id` = ?i LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, 0, $company_id, 1000);

		$output_list = [];
		foreach ($list as $v) {
			$output_list[] = self::_rowToObject($v);
		}

		return $output_list;
	}

	/**
	 * получает таблицу
	 *
	 */
	protected static function _getTableKey(int $user_id):string {

		return self::_TABLE_KEY . "_" . ceil($user_id / 1000000);
	}

	/**
	 * Группируем список компаний по шарду таблиц
	 *
	 */
	protected static function _groupedUserIdListByTableName(array $user_id_list):array {

		$grouped_user_id_list = [];
		foreach ($user_id_list as $user_id) {
			$grouped_user_id_list[self::_TABLE_KEY . "_" . ceil($user_id / 1000000)][] = $user_id;
		}

		return $grouped_user_id_list;
	}

	/**
	 * Создаем сткруктуру из строки бд
	 *
	 */
	protected static function _rowToObject(array $row):Struct_Db_PivotUser_UserLastCall {

		$extra = fromJson($row["extra"]);

		return new Struct_Db_PivotUser_UserLastCall(
			$row["user_id"],
			$row["company_id"],
			$row["call_key"],
			$row["is_finished"],
			$row["type"],
			$row["created_at"],
			$row["updated_at"],
			$extra,
		);
	}
}