<?php declare(strict_types=1);

namespace Compass\Conversation;

use BaseFrame\Server\ServerProvider;

/**
 * Класс для работы с поисковиком.
 */
class Gateway_Search_Main {

	public const TABLE_NAME = "main";
	public const REPLACE_BATCH_SIZE = 1000;

	/**
	 * Выполняет вставку записей для индексации.
	 * @param Struct_Domain_Search_Insert[] $insert_item_list
	 */
	public static function insert(array $insert_item_list):void {

		// приводим элементы к массиву
		$insert = array_map(static fn(Struct_Domain_Search_Insert $item) => (array) $item, $insert_item_list);
		ShardingGateway::search()->insert(static::_getTableNameWithCluster(), $insert);
	}

	/**
	 * Обновляет данные записи.
	 * При обновлении данные родителей не изменяются!
	 *
	 * @return int число обновленных записей
	 */
	public static function replace(Struct_Domain_Search_Replace $replace_item, int $per_iteration_limit = self::REPLACE_BATCH_SIZE):int {

		$offset = 0;
		$count  = 0;

		do {

			// получаем все записи, связанные указанным search_id
			$query      = "SELECT * FROM ?t WHERE `search_id` = ?i LIMIT ?i OFFSET ?i";
			$fetch_list = ShardingGateway::search()->select($query, [static::_getTableName(), $replace_item->search_id, $per_iteration_limit, $offset]);

			$replace = [];

			// заменяем значения полей и добавляем поля с текстом
			// заносим обновленную запись в массив для замены
			foreach ($fetch_list as $fetched_item) {

				if ($replace_item->updated_at > 0) {
					$fetched_item["updated_at"] = $replace_item->updated_at;
				}

				$fetched_item["field1"] = $replace_item->field1;
				$fetched_item["field2"] = $replace_item->field2;
				$fetched_item["field3"] = $replace_item->field3;
				$fetched_item["field4"] = $replace_item->field4;

				// кастим mva обратно в массив, из мантикоры он приходит строкой
				$fetched_item["inherit_parent_id_list"] = $fetched_item["inherit_parent_id_list"] !== ""
					? array_map(static fn(string $e):int => (int) $e, explode(",", $fetched_item["inherit_parent_id_list"]))
					: [];

				$replace[] = $fetched_item;
			}

			if (count($replace) > 0) {

				// обновляем записи в поисковике
				ShardingGateway::search()->replace(static::_getTableNameWithCluster(), $replace);

				$offset += static::REPLACE_BATCH_SIZE;
				$count  += count($replace);
			}
		} while (count($fetch_list) >= static::REPLACE_BATCH_SIZE);

		return $count;
	}

	/**
	 * Просто получает записи по их search_id.
	 * @return Struct_Domain_Search_HitRow[]
	 */
	public static function getRows(int $user_id, string $search_query, array $search_id_list):array {

		// формируем строку с независимыми полями
		$search_query = static::_makeQueryStringWithIndependentFields($search_query);

		$query = "
		SELECT 
    		`search_id`, 
    		`type`, 
    		`attribute_mask`,
    		`creator_id`,
    		`parent_id`, 
    		`updated_at`, 
    		`inherit_parent_id_list`, 
    		RANKFACTORS() AS `rankfactors`
		FROM ?t 
		WHERE MATCH(?s) 	
			AND `user_id` = ?i 
			AND `search_id` IN (?an) 
		LIMIT ?i OFFSET ?i
		";

		$result = ShardingGateway::search()->select($query, [static::_getTableName(), $search_query, $user_id, $search_id_list, count($search_id_list), 0]);
		return array_map(static fn(array $el):Struct_Domain_Search_HitRow => Struct_Domain_Search_HitRow::fromRow($el), $result);
	}

	/**
	 * Выполняет поиск по индексированным сущностям.
	 * @return Struct_Domain_Search_HitRow[]
	 * @long большие объявления
	 */
	public static function getHits(int $user_id, array $hit_type_list, int $parent_id, string $search_query, array $exclude_search_id_list, array $exclude_parent_search_id_list, int $limit, int $offset):array {

		// получем лимит совпадений для выборки
		$max_match_count = Domain_Search_Config_Query::getHitPerSearchMaxMatches();

		// если лимит больше ограничения поиска, то ничего не ищем
		if ($offset >= $max_match_count) {
			return [];
		}

		// формируем строку с независимыми полями
		$search_query = static::_makeQueryStringWithIndependentFields($search_query);

		$exclude_search_id_list_expression = "";
		$exclude_parent_id_list_expression = "";

		$params = [
			"table_name"                 => static::_getTableName(),
			"query"                      => $search_query,
			"user_id"                    => $user_id,
			"type"                       => $hit_type_list,
			"inherit_parent_id"          => $parent_id,
			"not_search_id_list"         => $exclude_search_id_list,
			"not_parent_id"              => $exclude_parent_search_id_list,
			"not_inherit_parent_id_list" => $exclude_parent_search_id_list,
			"limit"                      => $limit,
			"offset"                     => $offset,
			"ranker"                     => "1",
			"max_matches"                => $max_match_count
		];

		if (count($exclude_search_id_list)) {
			$exclude_search_id_list_expression = "AND search_id NOT IN (?an)";
		} else {
			unset($params["not_search_id_list"]);
		}

		if (count($exclude_parent_search_id_list)) {
			$exclude_parent_id_list_expression = "AND `parent_id` NOT IN (?an) AND ALL(`inherit_parent_id_list`) NOT IN (?an)";
		} else {

			unset($params["not_parent_id"]);
			unset($params["not_inherit_parent_id_list"]);
		}

		$query = "
			SELECT 
				`search_id`,
				`type`,
				`attribute_mask`,
				`creator_id`,
				`parent_id`,
				`updated_at`,
				`inherit_parent_id_list`,
				RANKFACTORS() AS `rankfactors`
			FROM ?t 
			WHERE MATCH(?s)
				AND `user_id` = ?i 
				AND `type` IN (?an)
				AND ANY(`inherit_parent_id_list`) = ?i
				$exclude_search_id_list_expression
				$exclude_parent_id_list_expression
			ORDER BY `updated_at` DESC
			LIMIT ?i OFFSET ?i
			OPTION ranker=export(?s), max_matches=?i
		";

		$result = ShardingGateway::search()->select($query, $params);
		return array_map(static fn(array $el):Struct_Domain_Search_HitRow => Struct_Domain_Search_HitRow::fromRow($el), $result);
	}

	/**
	 * Возвращает записи локаций.
	 * Сортирует локации по последнему совпадению.
	 *
	 * @param int $user_id ид пользователя, осуществляющего поиск
	 * @param array $location_type_list тип локаций, для которых ведется поиск
	 * @param array $hit_type_list типы совпадений, попадающих в выборку
	 * @param string $search_query подготовленная строка запроса
	 * @param int $limit лимит выборки
	 * @param int $offset смещение выборки
	 *
	 * @return Struct_Domain_Search_LocationRow[]
	 */
	public static function getLocations(int $user_id, array $location_type_list, array $hit_type_list, string $search_query, int $limit, int $offset):array {

		// получем лимит совпадений для выборки
		$max_match_count = Domain_Search_Config_Query::getLocationPerSearchMaxMatches();

		// если лимит больше ограничения поиска, то ничего не ищем
		if ($offset >= $max_match_count) {
			return [];
		}

		// формируем строку с независимыми полями
		$search_query = static::_makeQueryStringWithIndependentFields($search_query);

		$query = "
			SELECT
				`group_by_conversation_parent_id` AS `parent_id`,
				`type`,
				`parent_type_mask` & ?i AS `is_required_parent`,
				MAX(`updated_at`) AS `last_updated_at`,
				COUNT(*) AS `hit_count`
			FROM ?t
			WHERE 
				MATCH(?s)
				AND `user_id` = ?i
				AND `is_required_parent` > ?i
				AND `type` IN (?an)
			GROUP BY `group_by_conversation_parent_id`
			ORDER BY `last_updated_at` DESC
			LIMIT ?i OFFSET ?i
			OPTION max_matches=?i
		";

		$params = [
			"parent_type_mask"   => array_reduce($location_type_list, static fn(int $carry, int $item) => $carry | $item, 0),
			"table_name"         => static::_getTableName(),
			"query"              => $search_query,
			"user_id"            => $user_id,
			"is_required_parent" => 0,
			"type"               => $hit_type_list,
			"limit"              => $limit,
			"offset"             => $offset,
			"max_matches"        => $max_match_count,
		];

		$result = ShardingGateway::search()->select($query, array_values($params));
		return array_map(static fn(array $el):Struct_Domain_Search_LocationRow => Struct_Domain_Search_LocationRow::fromRow($el), $result);
	}

	/**
	 * Возвращает записи локаций по id родителя.
	 * Сортирует локации по последнему совпадению.
	 *
	 * @return Struct_Domain_Search_LocationRow[]
	 * @throws \BaseFrame\Search\Exception\ExecutionException
	 * @long
	 */
	public static function getLocationsByParentId(int $user_id, array $location_type_list, array $hit_type_list, array $parent_id_list, string $search_query, int $limit, int $offset):array {

		// получем лимит совпадений для выборки
		$max_match_count = Domain_Search_Config_Query::getLocationPerSearchMaxMatches();

		// если лимит больше ограничения поиска, то ничего не ищем
		if ($offset >= $max_match_count) {
			return [];
		}

		// формируем строку с независимыми полями
		$search_query = static::_makeQueryStringWithIndependentFields($search_query);

		$query = "
			SELECT
				`group_by_conversation_parent_id` AS `parent_id`,
				`type`,
				`parent_type_mask` & ?i AS `is_required_parent`,
				MAX(`updated_at`) AS `last_updated_at`,
				COUNT(*) AS `hit_count`
			FROM ?t
			WHERE 
				MATCH(?s)
				AND `user_id` = ?i
				AND `is_required_parent` > ?i
				AND `type` IN (?an)
				AND `parent_id` IN (?an)
			GROUP BY `group_by_conversation_parent_id`
			ORDER BY `last_updated_at` DESC
			LIMIT ?i OFFSET ?i
			OPTION max_matches=?i
		";

		$params = [
			"parent_type_mask"   => array_reduce($location_type_list, static fn(int $carry, int $item) => $carry | $item, 0),
			"table_name"         => static::_getTableName(),
			"query"              => $search_query,
			"user_id"            => $user_id,
			"is_required_parent" => 0,
			"type"               => $hit_type_list,
			"parent_id"          => $parent_id_list,
			"limit"              => $limit,
			"offset"             => $offset,
			"max_matches"        => $max_match_count,
		];

		$result = ShardingGateway::search()->select($query, array_values($params));
		return array_map(static fn(array $el):Struct_Domain_Search_LocationRow => Struct_Domain_Search_LocationRow::fromRow($el), $result);
	}

	/**
	 * Удаляем запись и всех привязанных детей.
	 */
	public static function delete(int $parent_search_id):void {

		$query = "DELETE FROM ?t WHERE `search_id` = ?i OR ANY(`inherit_parent_id_list`) = ?i";
		ShardingGateway::search()->delete($query, [static::_getTableNameWithCluster(), $parent_search_id, $parent_search_id]);
	}

	/**
	 * Удаляем запись и всех привязанных детей для указанного списка пользователей.
	 */
	public static function deleteForUsers(int $parent_search_id, array $user_id_list):void {

		$query = "DELETE FROM ?t WHERE `user_id` IN (?an) AND (`search_id` = ?i OR ANY(`inherit_parent_id_list`) = ?i)";
		ShardingGateway::search()->delete($query, [static::_getTableNameWithCluster(), $user_id_list, $parent_search_id, $parent_search_id]);
	}

	/**
	 * Удаляет все связанные сущности по родителю.
	 */
	public static function deleteByParent(int $parent_search_id):void {

		$query = "DELETE FROM ?t WHERE ANY(`inherit_parent_id_list`) = ?i";
		ShardingGateway::search()->delete($query, [static::_getTableNameWithCluster(), $parent_search_id]);
	}

	/**
	 * Удаляет записи указанных типов по их direct_parent_id.
	 */
	public static function deleteTypedByParent(int $parent_search_id, array $type_list):void {

		$query = "DELETE FROM ?t WHERE `parent_id` = ?i AND `type` IN (?an)";
		ShardingGateway::search()->delete($query, [static::_getTableNameWithCluster(), $parent_search_id, $type_list]);
	}

	/**
	 * Удаляем все записи, для которых указанная сущность является родительской.
	 * Работает для указанного списка пользователей. Применяется для переиндексации.
	 */
	public static function deleteByParentForUsers(int $parent_search_id, array $user_id_list):void {

		$query = "DELETE FROM ?t WHERE `user_id` IN (?an) AND ANY(`inherit_parent_id_list`) = ?i";
		ShardingGateway::search()->delete($query, [static::_getTableNameWithCluster(), $user_id_list, $parent_search_id]);
	}

	/**
	 * Возвращает полное число совпадений для предыдущего запроса.
	 */
	public static function fetchTotalCountFromLastRequest():int {

		$meta = ShardingGateway::search()->query("SHOW META")->fetchAll();

		foreach ($meta as $meta_data) {

			if ($meta_data["Variable_name"] === "total_found") {
				return (int) ($meta_data["Value"] ?? 0);
			}
		}

		return 0;
	}

	/**
	 * Очищает индекс.
	 * Помянем.
	 */
	public static function truncate():void {

		$table = static::_getTableNameWithCluster();
		$table = str_replace(":", "`:`", $table);
		ShardingGateway::search()->query("TRUNCATE TABLE `$table`");
	}

	/**
	 * Формирует поисковый запрос с независимыми полями.
	 */
	protected static function _makeQueryStringWithIndependentFields(string $search_query):string {

		// расширяем поисковую строку, чтобы совпадения выбирались по отдельным полям
		// если этого не сделать, то запрос из нескольких слов может заматчиться в разных полях
		return "@field1 $search_query | @field2 $search_query";
	}

	/**
	 * Возвращает имя таблицы.
	 */
	protected static function _getTableName(int $space_id = COMPANY_ID):string {

		return static::TABLE_NAME . "_$space_id";
	}

	/**
	 * Возвращает имя таблицы с кластером (для репликации мантикоры).
	 * Из документации:
	 * При работе с кластером репликации все операторы записи, такие как INSERT, REPLACE, DELETE, TRUNCATE, UPDATE
	 * которые изменяют содержимое таблицы кластера, должны использовать cluster_name:table_name выражение вместо имени таблицы
	 */
	protected static function _getTableNameWithCluster(int $space_id = COMPANY_ID):string {

		$table_name = self::_getTableName($space_id);

		if (!ServerProvider::isOnPremise() || mb_strlen(SERVICE_LABEL) < 1) {
			return $table_name;
		}

		if (!defined("MANTICORE_CLUSTER_NAME") || mb_strlen(MANTICORE_CLUSTER_NAME) < 1) {
			return $table_name;
		}

		return MANTICORE_CLUSTER_NAME . ":" . $table_name;
	}
}
