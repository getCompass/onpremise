<?php

namespace Compass\Premise;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс-интерфейс для таблицы pivot_user_{10m}.user_list_{1}
 */
class Gateway_Db_PivotUser_UserList extends Gateway_Db_PivotUser_Main {

	protected const _TABLE_KEY                   = "user_list";
	protected const _USER_ID_TABLE_SHARDING_STEP = 1000000;

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Получить запись пользователя
	 *
	 * @param int $user_id
	 *
	 * @return Struct_Db_PivotUser_User
	 * @throws ParseFatalException
	 * @throws RowNotFoundException
	 */
	public static function getOne(int $user_id):Struct_Db_PivotUser_User {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id`=?i LIMIT ?i";

		$row = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, 1);

		if (!isset($row["user_id"])) {
			throw new RowNotFoundException("user not found");
		}

		return self::_rowToStruct($row);
	}

	/**
	 * Получаем список пользователей
	 *
	 * @param array $user_id_list
	 *
	 * @return Struct_Db_PivotUser_User[]
	 * @throws ParseFatalException
	 */
	public static function getList(array $user_id_list):array {

		$result_rows           = [];
		$grouped_by_shard_list = [];

		foreach ($user_id_list as $user_id) {

			$key                           = sprintf("%s.%s", self::_getDbKey($user_id), self::_getTableKey($user_id));
			$grouped_by_shard_list[$key][] = $user_id;
		}

		$query = "SELECT * FROM `?p` WHERE `user_id` IN (?a) LIMIT ?i";

		// делаем запросы
		$grouped_query_list = [];
		foreach ($grouped_by_shard_list as $key => $user_id_list) {

			// формируем и осуществляем запрос
			[$shard_key, $table_name] = explode(".", $key);
			$grouped_query_list[] = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id_list, count($user_id_list));
		}

		// собираем массив объектов
		foreach ($grouped_query_list as $query_list) {

			foreach ($query_list as $row) {
				$result_rows[] = self::_rowToStruct($row);
			}
		}

		return $result_rows;
	}

	/**
	 * Найти по имени
	 *
	 * @param array $search_word_list
	 * @param int   $limit
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \queryException
	 * @long
	 */
	public static function findByFullName(array $search_word_list, int $limit):array {

		$shard_list = self::_getShardList();

		// подготавливаем поисковый запрос
		// + для того, что в имени обязано находится слово, которое ищем
		// * - любоое количество произвольных символов в конце слова
		$placeholders    = trim(str_repeat("+%s* ", count($search_word_list)));
		$query_condition = sprintf(" MATCH (`full_name`) AGAINST ('{$placeholders}' IN BOOLEAN MODE)", ...$search_word_list);

		// проходимся по каждому шарду, пока не найдем $limit пользователей
		$initial_limit = $limit;
		$user_list     = [];
		foreach ($shard_list as $shard) {

			[$db_key, $table_name] = explode(".", $shard);

			// осуществляем запрос
			// запрос проверен на EXPLAIN (INDEX=full_name.search). Федореев М 05.04.2024
			$query = "SELECT * FROM `?p` USE INDEX (`full_name.search`) WHERE ?p ORDER BY user_id DESC LIMIT ?i";
			$list  = ShardingGateway::database($db_key)->getAll($query, $table_name, $query_condition, $limit);

			// конвертируем найденные строки
			$user_list = array_merge($user_list, array_map(static fn(array $row) => self::_rowToStruct($row), $list));

			// определяем, нужно ли искать еще
			$limit -= count($list);
			if ($limit < 1) {
				break;
			}
		}

		return array_slice($user_list, 0, $initial_limit);
	}

	/**
	 * Получить несколько записей по указанному лимиту
	 *
	 * @param int $limit
	 *
	 * @return Struct_Db_PivotUser_User[]
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function getByLimit(int $limit):array {

		$shard_list = self::_getShardList();

		$initial_limit = $limit;
		$user_list     = [];
		foreach ($shard_list as $shard) {

			[$db_key, $table_name] = explode(".", $shard);

			// запрос проверен на EXPLAIN (INDEX=PRIMARY) Котов В.В. 18.03.2024
			$query = "SELECT * FROM `?p` WHERE TRUE ORDER BY `user_id` DESC LIMIT ?i";
			$list  = ShardingGateway::database($db_key)->getAll($query, $table_name, $limit);

			// конвертируем найденные строки
			$user_list = array_merge($user_list, array_map(static fn(array $row) => self::_rowToStruct($row), $list));

			// определяем, нужно ли искать еще
			$limit -= count($list);
			if ($limit < 1) {
				break;
			}
		}

		return array_slice($user_list, 0, $initial_limit);
	}

	/**
	 * Получить все записи
	 * Работает только с pivot_user_10m.user_list_1
	 *
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return Struct_Db_PivotUser_User[]
	 * @throws ParseFatalException
	 */
	public static function getAll(int $limit, int $offset = 0):array {

		$shard_key  = self::_getDbKey(1);
		$table_name = self::_getTableKey(1);

		// запрос проверен на EXPLAIN (INDEX=PRIMARY) Котов В.В. 18.03.2024
		$query = "SELECT * FROM `?p` WHERE TRUE ORDER BY `user_id` DESC LIMIT ?i OFFSET ?i";
		$rows  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $limit, $offset);

		if (count($rows) === 0) {
			return [];
		}

		$result_rows = [];

		foreach ($rows as $row) {
			$result_rows[] = self::_rowToStruct($row);
		}

		return $result_rows;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Получить список доступных шардов
	 *
	 * @return string[]
	 * @throws \queryException
	 */
	protected static function _getShardList():array {

		// получаем ID последнего зарегистрированного пользователя
		$last_user_id = Gateway_Db_PivotSystem_AutoIncrement::get(Gateway_Db_PivotSystem_AutoIncrement::USER_ID_KEY);

		// получаем шарды всех БД и таблиц, в которых имеются данные
		// ориентируемся на $last_user_id – максимально существующий ID пользователя
		$shard_list    = [];
		$start_user_id = 1;
		while ($start_user_id < $last_user_id) {

			$shard_list[]  = sprintf("%s.%s", self::_getDbKey($start_user_id), self::_getTableKey($start_user_id));
			$start_user_id += self::_USER_ID_TABLE_SHARDING_STEP;
		}
		$shard_list = array_unique($shard_list);

		return array_reverse($shard_list);
	}

	/**
	 * получает таблицу
	 *
	 */
	protected static function _getTableKey(int $user_id):string {

		return self::_TABLE_KEY . "_" . ceil($user_id / 1000000);
	}

	/**
	 * Форматирует запись в структуру
	 *
	 */
	protected static function _rowToStruct(array $row):Struct_Db_PivotUser_User {

		return new Struct_Db_PivotUser_User(
			$row["user_id"],
			$row["npc_type"],
			$row["invited_by_partner_id"],
			$row["invited_by_user_id"],
			$row["last_active_day_start_at"],
			$row["created_at"],
			$row["updated_at"],
			$row["full_name_updated_at"],
			$row["country_code"],
			$row["full_name"],
			$row["avatar_file_map"],
			fromJson($row["extra"])
		);
	}
}