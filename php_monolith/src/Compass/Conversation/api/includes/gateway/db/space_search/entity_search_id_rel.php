<?php

namespace Compass\Conversation;

/**
 * Класс-интерфейс для таблицы entity_search_id_rel_{f}.
 * Работает со связями entity_id — search_id.
 */
class Gateway_Db_SpaceSearch_EntitySearchIdRel extends Gateway_Db_SpaceSearch_Main {

	protected const _TABLE_KEY = "entity_search_id_rel";

	/**
	 * Возвращает число записей в таблице, нужно для определния необходимости переиндексации.
	 */
	public static function count():int {

		// получаем список всех таблиц, где будем искать
		$table_name_list = array_map(static fn(int $num):string => self::_getTableNameBySuffix(dechex($num)), range(0, 15));

		// пробегаемся по каждой и считаем
		$result = 0;
		foreach ($table_name_list as $table_name) {

			// EXPLAIN: INDEX PRIMARY
			$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE TRUE LIMIT ?i";
			$row   = static::_connect()->getOne($query, $table_name, 1);

			$result += $row["count"] ?? 0;
		}

		return (int) $result;
	}

	/**
	 * Добавляет одну запись, возвращает search_id для вставленной записи.
	 */
	public static function insert(int $entity_type, string $entity_map):int {

		return static::insertList([new Struct_Domain_Search_AppEntity($entity_type, $entity_map)]);
	}

	/**
	 * Добавляет одну запись, возвращает search_id для вставленной записи.
	 *
	 * @param Struct_Domain_Search_AppEntity[] $entity_list
	 */
	public static function insertList(array $entity_list):int {

		// пробегаемся по каждой записи и группируем по таблицам
		$grouped_by_table = [];
		foreach ($entity_list as $entity) {

			// получаем entity_id
			$entity_id = self::getEntityId($entity->entity_map);

			// получили таблицу
			$table_name = self::_getTableNameByEntityId($entity_id);

			// сгруппировали
			$grouped_by_table[$table_name][] = [
				"entity_id"   => $entity_id,
				"entity_type" => $entity->entity_type,
				"entity_map"  => $entity->entity_map,
			];
		}

		// пишем в базу
		$result = 0;
		foreach ($grouped_by_table as $table_name => $grouped_entity_list) {
			$result += static::_connect()->insertArray($table_name, $grouped_entity_list);
		}

		return $result;
	}

	/**
	 * Добавляет одну запись, возвращает search_id для вставленной записи.
	 *
	 * @param int[] $search_id_list
	 *
	 * @return array
	 */
	public static function getSearched(array $search_id_list):array {

		// группируем по таблица все получаемые сущности
		$grouped_by_table = [];
		foreach ($search_id_list as $search_id) {

			// получаем таблицу
			$table_name = self::_getTableNameBySearchId($search_id);

			// группируем
			$grouped_by_table[$table_name][] = $search_id;
		}

		$output = [];
		foreach ($grouped_by_table as $table_name => $grouped_search_id_list) {

			// EXPLAIN: INDEX search_id
			$query = "SELECT * FROM `?p` WHERE `search_id` IN (?a) LIMIT ?i";
			$list  = static::_connect()->getAll($query, $table_name, $grouped_search_id_list, count($grouped_search_id_list));

			// складываем в ответ
			$output = array_merge($output, $list);
		}

		return array_map(static fn(array $row):Struct_Db_SpaceSearch_EntitySearchIdRel => static::_fromRow($row), $output);
	}

	/**
	 * Выбираем search_id из базы
	 * В ответе ассоциативный массив вида [ $entity_id => $search_id, ]
	 *
	 * @param string[] $entity_map_list
	 *
	 * @return array
	 */
	public static function getIndexed(array $entity_map_list):array {

		// группируем по таблица все получаемые сущности
		$grouped_by_table = [];
		foreach ($entity_map_list as $entity_map) {

			// получаем entity_id
			$entity_id = self::getEntityId($entity_map);

			// получаем таблицу
			$table_name = self::_getTableNameByEntityId($entity_id);

			// группируем
			$grouped_by_table[$table_name][] = $entity_id;
		}

		$output = [];
		foreach ($grouped_by_table as $table_name => $grouped_entity_id_list) {

			// ВАЖНО! Здесь выбираем только entity_id, search_id и все! лишние поля здесь не нужны
			// а их подтягивание несет overload на mysql и снижает профит от индексов
			// EXPLAIN: INDEX PRIMARY
			$query = "SELECT `entity_id`, `search_id` FROM `?p` WHERE `entity_id` IN (?a) LIMIT ?i";
			$list  = static::_connect()->getAllKeyPair($query, $table_name, $grouped_entity_id_list, count($grouped_entity_id_list));

			$output += $list;
		}

		return $output;
	}

	/**
	 * Получаем название таблицы с которой будем работать
	 *
	 * @return string
	 */
	protected static function _getTableNameByEntityId(string $entity_id):string {

		// получаем суффикс шарда
		$shard_suffix = $entity_id[0];

		return self::_getTableNameBySuffix($shard_suffix);
	}

	/**
	 * Получаем название таблицы с которой будем работать
	 *
	 * @return string
	 */
	protected static function _getTableNameBySearchId(int $search_id):string {

		// получаем суффикс шарда
		$shard_suffix = dechex(floor($search_id / 1000000000));

		return self::_getTableNameBySuffix($shard_suffix);
	}

	/**
	 * Получаем название таблицы с помощью суффикса шарда
	 *
	 * @return string
	 */
	protected static function _getTableNameBySuffix(string $shard_suffix):string {

		return static::_TABLE_KEY . "_" . $shard_suffix;
	}

	/**
	 * Конвертирует строку записи из бд в объект.
	 */
	protected static function _fromRow(array $row):Struct_Db_SpaceSearch_EntitySearchIdRel {

		return new Struct_Db_SpaceSearch_EntitySearchIdRel(
			(string) $row["entity_id"],
			(int) $row["search_id"],
			(int) $row["entity_type"],
			(string) $row["entity_map"],
		);
	}

	/**
	 * Получаем entity_id
	 *
	 * @return string
	 */
	public static function getEntityId(string $entity_map):string {

		return sha1($entity_map);
	}
}