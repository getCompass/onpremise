<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс-интерфейс для таблицы company_conversation.user_left_menu
 */
class Gateway_Db_CompanyConversation_UserLeftMenu extends Gateway_Db_CompanyConversation_Main {

	protected const _TABLE_KEY            = "user_left_menu";
	protected const _LEFT_MENU_INDEX_NAME = "get_left_menu";

	// создание записи
	public static function insert(array $insert):void {

		$table = self::_getTable();

		static::_connect(self::_getDbKey())->insert($table, $insert);
	}

	// получение записи
	public static function getOne(int $user_id, string $conversation_map):array {

		$table = self::_getTable();

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query         = "SELECT * FROM `?p` WHERE user_id = ?i AND `conversation_map` = ?s LIMIT ?i";
		$left_menu_row = static::_connect(self::_getDbKey())->getOne($query, $table, $user_id, $conversation_map, 1);

		// если пришел существующий left_menu для user_id - отдаем последнее сообщение
		if (isset($left_menu_row["user_id"])) {
			$left_menu_row["last_message"] = fromJson($left_menu_row["last_message"]);
		}

		return $left_menu_row;
	}

	// получение записи на обновление
	public static function getForUpdate(int $user_id, string $conversation_map):array {

		$table = self::_getTable();

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query         = "SELECT * FROM `?p` WHERE user_id = ?i AND `conversation_map` = ?s LIMIT ?i FOR UPDATE";
		$left_menu_row = static::_connect(self::_getDbKey())->getOne($query, $table, $user_id, $conversation_map, 1);

		// пришел существующий left_menu?
		if (isset($left_menu_row["user_id"])) {
			$left_menu_row["last_message"] = fromJson($left_menu_row["last_message"]);
		}

		return $left_menu_row;
	}

	// получение нескольких записей по массиву диалогов
	public static function getList(int $user_id, array $conversation_map_list, bool $is_assoc = false):array {

		$table = self::_getTable();

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query          = "SELECT * FROM `?p` WHERE user_id = ?i AND `conversation_map` IN (?a) ORDER BY `is_favorite` DESC, `updated_at` DESC LIMIT ?i";
		$left_menu_list = static::_connect(self::_getDbKey())->getAll($query, $table, $user_id, $conversation_map_list, count($conversation_map_list));

		$output = [];
		foreach ($left_menu_list as $k => $v) {

			$v["last_message"] = fromJson($v["last_message"]);
			if ($is_assoc) {
				$output[$v["conversation_map"]] = $v;
			} else {
				$output[$k] = $v;
			}
		}

		return $output;
	}

	/**
	 * Получить все группы пользователя
	 *
	 * @param int $user_id
	 *
	 * @return array
	 */
	public static function getGroupsByUser(int $user_id):array {

		$table = self::_getTable();

		// запрос проверен на EXPLAIN (INDEX=get_left_menu)
		$query          = "SELECT * FROM `?p` WHERE user_id = ?i AND `is_hidden` = ?i AND type IN (?a) LIMIT ?i";
		$left_menu_list = static::_connect(self::_getDbKey())->getAll(
			$query, $table, $user_id, 0, [CONVERSATION_TYPE_GROUP_DEFAULT, CONVERSATION_TYPE_GROUP_GENERAL, CONVERSATION_TYPE_GROUP_RESPECT], 10000);

		$output = [];
		foreach ($left_menu_list as $v) {

			$v["last_message"]              = fromJson($v["last_message"]);
			$output[$v["conversation_map"]] = $v;
		}

		return $output;
	}

	/**
	 * Получить группу службы поддержки пользователя
	 *
	 * @param int $user_id
	 *
	 * @return array
	 * @throws RowNotFoundException
	 */
	public static function getSupportGroupByUser(int $user_id):array {

		$table = self::_getTable();

		// запрос проверен на EXPLAIN (INDEX=get_left_menu)
		$query         = "SELECT * FROM `?p` FORCE INDEX (`get_left_menu`) WHERE user_id = ?i AND `is_hidden` = ?i AND `type` = ?i LIMIT ?i";
		$left_menu_row = static::_connect(self::_getDbKey())->getOne($query, $table, $user_id, 0, CONVERSATION_TYPE_GROUP_SUPPORT, 1);

		if (!isset($left_menu_row["user_id"])) {
			throw new RowNotFoundException("row not found");
		}

		$left_menu_row["last_message"] = fromJson($left_menu_row["last_message"]);
		return $left_menu_row;
	}

	// получение нескольких записей по массиву диалогов
	public static function getListFromLeftMenu(int $user_id, array $conversation_map_list):array {

		$table = self::_getTable();

		// проверил в EXPLAIN: key=PRIMARY
		$query          = "SELECT * FROM `?p` WHERE user_id = ?i AND `conversation_map` IN (?a) AND `is_hidden` = ?i AND `is_leaved` = ?i ORDER BY `is_favorite` DESC, `updated_at` DESC LIMIT ?i";
		$left_menu_list = static::_connect(self::_getDbKey())->getAll($query, $table, $user_id, $conversation_map_list, 0, 0, count($conversation_map_list));

		foreach ($left_menu_list as $k => $v) {
			$left_menu_list[$k]["last_message"] = fromJson($v["last_message"]);
		}

		return $left_menu_list;
	}

	// получение нескольких записей по массиву диалогов
	public static function getListFromLeftMenuWithoutBlocked(int $user_id, array $conversation_map_list):array {

		$table = self::_getTable();

		// проверил EXPLAIN (index=get_left_menu)
		$query          = "SELECT * FROM `?p` WHERE user_id = ?i  AND `conversation_map` IN (?a) AND `is_hidden` = ?i AND `is_leaved` = ?i AND `allow_status_alias` IN (?a) ORDER BY `is_favorite` DESC, `is_mentioned` DESC, `updated_at` DESC LIMIT ?i";
		$left_menu_list = static::_connect(self::_getDbKey())->getAll($query, $table, $user_id, $conversation_map_list, 0, 0, [0, Type_Conversation_Utils::ALLOW_STATUS_OK], count($conversation_map_list));

		foreach ($left_menu_list as $k => $v) {
			$left_menu_list[$k]["last_message"] = fromJson($v["last_message"]);
		}

		return $left_menu_list;
	}

	// получение количества диалогов из левого меню
	public static function getCountLeftMenu(int $user_id):int {

		$table = self::_getTable();

		// получаем количество диалогов левого меню
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query_count = "SELECT COUNT(*) AS COUNT FROM `?p` WHERE user_id = ?i LIMIT ?i";

		return static::_connect(self::_getDbKey())->getOne($query_count, $table, $user_id, 1)["COUNT"];
	}

	// возвращает список диалогов left_menu по offset
	public static function getByOffset(int $user_id, int $limit, int $offset):array {

		$table = self::_getTable();

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query          = "SELECT * FROM `?p` WHERE user_id = ?i LIMIT ?i OFFSET ?i";
		$left_menu_list = static::_connect(self::_getDbKey())->getAll($query, $table, $user_id, $limit, $offset);

		foreach ($left_menu_list as $k => $v) {
			$left_menu_list[$k]["last_message"] = fromJson($v["last_message"]);
		}

		return $left_menu_list;
	}

	// обновление записи
	public static function set(int $user_id, string $conversation_map, array $set):void {

		$table_name = self::_getTable();

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "UPDATE `?p` SET ?u WHERE user_id = ?i AND `conversation_map` = ?s LIMIT ?i";
		static::_connect(self::_getDbKey())->update($query, $table_name, $set, $user_id, $conversation_map, 1);
	}

	// обновление записи
	public static function setUserIdList(array $user_id_list, string $conversation_map, array $set):void {

		$table_name = self::_getTable();

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "UPDATE `?p` SET ?u WHERE user_id IN (?a) AND `conversation_map` = ?s LIMIT ?i";
		static::_connect(self::_getDbKey())->update($query, $table_name, $set, $user_id_list, $conversation_map, count($user_id_list));
	}

	// обновление записи для списка пользователей
	public static function setForUserIdList(array $user_id_list, string $conversation_map, array $set):void {

		// группируем пользователей по местонахождению в таблицах
		$groped_by_table_user_id_list = [];
		foreach ($user_id_list as $user_id) {

			$table_name                                  = self::_getTable();
			$groped_by_table_user_id_list[$table_name][] = $user_id;
		}

		// проходимся по каждой таблице с пользователями из списка
		foreach ($groped_by_table_user_id_list as $table_name => $user_id_list) {

			// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
			$query = "UPDATE `?p` SET ?u WHERE user_id IN (?a) AND `conversation_map` = ?s LIMIT ?i";
			static::_connect(self::_getDbKey())->update($query, $table_name, $set, $user_id_list, $conversation_map, count($user_id_list));
		}
	}

	/**
	 * обновляем несколько записей
	 *
	 */
	public static function setList(int $user_id, array $conversation_map_list, array $set):void {

		$table = self::_getTable();

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "UPDATE `?p` SET ?u WHERE user_id = ?i AND `conversation_map` IN (?a) LIMIT ?i";
		static::_connect(self::_getDbKey())->update($query, $table, $set, $user_id, $conversation_map_list, count($conversation_map_list));
	}

	/**
	 * Получение не избранных диалогов из left_menu (при этом is_leaved = 0 & is_hidden = 0)
	 *
	 */
	public static function getLeftMenu(int $user_id, int $favorite_filter, int $count, int $offset):array {

		$table          = self::_getTable();
		$left_menu_list = [];

		// -1 - без избранных, 0 - все чаты, 1 - только избранные
		switch ($favorite_filter) {

			case -1:
			case 1:

				// запрос проверен на EXPLAIN (INDEX=get_left_menu)
				$query          = "SELECT * FROM `?p` USE INDEX (`?p`) WHERE user_id = ?i AND `is_favorite` = ?i AND `is_hidden` = ?i AND `is_leaved` = ?i ORDER BY `is_mentioned` DESC, `updated_at` DESC LIMIT ?i OFFSET ?i";
				$left_menu_list = static::_connect(self::_getDbKey())->getAll($query, $table, self::_LEFT_MENU_INDEX_NAME, $user_id, $favorite_filter == -1 ? 0 : 1, 0, 0, $count, $offset);
				break;
			case 0:

				// запрос проверен на EXPLAIN (INDEX=get_left_menu)
				$query          = "SELECT * FROM `?p` USE INDEX (`?p`) WHERE user_id = ?i AND `is_hidden` = ?i AND `is_leaved` = ?i ORDER BY `is_mentioned` DESC, `updated_at` DESC LIMIT ?i OFFSET ?i";
				$left_menu_list = static::_connect(self::_getDbKey())->getAll($query, $table, self::_LEFT_MENU_INDEX_NAME, $user_id, 0, 0, $count, $offset);
				break;
		}

		// добавляем последнее сообщение к результату
		foreach ($left_menu_list as $k => $v) {
			$left_menu_list[$k]["last_message"] = fromJson($v["last_message"]);
		}

		return $left_menu_list;
	}

	// получение диалогов из left_menu (при этом is_leaved = 0 & is_hidden = 0 & allowed_status_alias = 0)
	public static function getLeftMenuWithoutBlocked(int $user_id, int $count, int $offset):array {

		$table = self::_getTable();

		// проверил EXPLAIN (index=get_left_menu)
		$query          = "SELECT * FROM `?p` FORCE INDEX (`?p`) WHERE user_id = ?i AND `is_hidden` = ?i AND `is_leaved` = ?i AND `allow_status_alias` IN (?a) ORDER BY `is_favorite` DESC, `is_mentioned` DESC, `updated_at` DESC LIMIT ?i OFFSET ?i";
		$left_menu_list = static::_connect(self::_getDbKey())->getAll($query, $table, self::_LEFT_MENU_INDEX_NAME, $user_id, 0, 0, [0, Type_Conversation_Utils::ALLOW_STATUS_OK], $count, $offset);

		// добавляем последнее сообщение к результату
		foreach ($left_menu_list as $k => $v) {
			$left_menu_list[$k]["last_message"] = fromJson($v["last_message"]);
		}

		return $left_menu_list;
	}

	/**
	 * Получить список непрочитанных чатов
	 *
	 */
	public static function getUnreadMenu(int $user_id, int $favorite_filter, int $count, int $offset):array {

		$table          = self::_getTable();
		$left_menu_list = [];

		// -1 - без избранных, 0 - все чаты, 1 - только избранные
		switch ($favorite_filter) {

			case -1:
			case 1:

				// запрос проверен на EXPLAIN (INDEX=get_left_menu)
				$query          = "SELECT * FROM `?p` USE INDEX (`?p`) WHERE user_id = ?i AND `is_favorite` = ?i AND `is_hidden` = ?i AND `unread_count` > ?i ORDER BY `is_mentioned` DESC, `updated_at` DESC LIMIT ?i OFFSET ?i";
				$left_menu_list = static::_connect(self::_getDbKey())->getAll($query, $table, self::_LEFT_MENU_INDEX_NAME, $user_id, $favorite_filter == -1 ? 0 : 1, 0, 0, $count, $offset);
				break;
			case 0:

				// запрос проверен на EXPLAIN (INDEX=get_left_menu)
				$query          = "SELECT * FROM `?p` USE INDEX (`?p`) WHERE user_id = ?i AND `is_hidden` = ?i AND `unread_count` > ?i ORDER BY `is_mentioned` DESC, `updated_at` DESC LIMIT ?i OFFSET ?i";
				$left_menu_list = static::_connect(self::_getDbKey())->getAll($query, $table, self::_LEFT_MENU_INDEX_NAME, $user_id, 0, 0, $count, $offset);
				break;
		}

		// добавляем последнее сообщение к результату
		foreach ($left_menu_list as $k => $v) {
			$left_menu_list[$k]["last_message"] = fromJson($v["last_message"]);
		}

		return $left_menu_list;
	}

	/**
	 * Получение левого меню выше определенной версии
	 *
	 */
	public static function getLeftMenuByVersion(int $user_id, int $version):array {

		$table = self::_getTable();

		// запрос проверен на EXPLAIN (INDEX=get_versioned_menu)
		$query          = "SELECT * FROM `?p` USE INDEX (`get_versioned_menu`) WHERE user_id = ?i AND `version` > ?i LIMIT ?i";
		$left_menu_list = static::_connect(self::_getDbKey())->getAll($query, $table, $user_id, $version, 1000);

		// добавляем последнее сообщение к результату
		foreach ($left_menu_list as $k => $v) {
			$left_menu_list[$k]["last_message"] = fromJson($v["last_message"]);
		}

		return $left_menu_list;
	}

	/**
	 * Получение левого меню выше определенной версии
	 *
	 */
	public static function getLeftMenuLastVersion(int $user_id):int {

		$table = self::_getTable();

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT MAX(`version`) as last_version FROM `?p` WHERE user_id = ?i LIMIT ?i";
		return (int) static::_connect(self::_getDbKey())->getOne($query, $table, $user_id, 1)["last_version"];
	}

	/**
	 * Вернуть левое меню с флагами
	 *
	 * @param int    $user_id
	 * @param int    $count
	 * @param int    $offset
	 * @param string $search_query
	 * @param int    $filter_favorite
	 * @param int    $filter_unread
	 * @param int    $filter_single
	 * @param int    $filter_unblocked
	 * @param int    $filter_owner
	 * @param int    $filter_system
	 * @param int    $is_mentioned_first
	 *
	 * @return array
	 * @long
	 */
	public static function getLeftMenuWithFlags(int $user_id, int $count, int $offset, string $search_query = "", int $filter_favorite = 0, int $filter_unread = 0, int $filter_single = 0, int $filter_unblocked = 0, int $filter_owner = 0, int $filter_system = 0, int $filter_support = 0, int $is_mentioned_first = 0):array {

		$table = self::_getTable();

		// сортировка для получения первым чат Поддержки
		$order_by_support = $filter_support == 1 ? " `type` = ?i DESC," : "";

		// запрос проверен на EXPLAIN (INDEX=get_left_menu)
		$query           = "SELECT * FROM `?p` USE INDEX (`get_left_menu`) WHERE `user_id` = ?i AND `is_hidden` = ?i";
		$order_by_clause = " ORDER BY{$order_by_support} `is_favorite` DESC, `is_mentioned` DESC, `updated_at` DESC LIMIT ?i OFFSET ?i";

		// если необходимо отдавать сначала упомянутых
		if ($is_mentioned_first) {

			// запрос проверен на EXPLAIN (INDEX=get_left_menu)
			// использование INDEX=get_left_menu в данном запросе согласовано
			$query           = "SELECT * FROM `?p` USE INDEX (`get_left_menu`) WHERE `user_id` = ?i AND `is_hidden` = ?i";
			$order_by_clause = " ORDER BY{$order_by_support} `is_mentioned` DESC, `is_favorite` DESC, `updated_at` DESC LIMIT ?i OFFSET ?i";
		}

		// если необходимо отдать непрочитанные диалоги
		if ($filter_unread) {

			// запрос проверен на EXPLAIN (INDEX=get_unread_menu)
			$query           = "SELECT * FROM `?p` USE INDEX (`get_unread_menu`) WHERE `user_id` = ?i AND `is_hidden` = ?i AND `is_have_notice` = ?i";
			$order_by_clause = " ORDER BY{$order_by_support} `is_mentioned` DESC, `updated_at` DESC LIMIT ?i OFFSET ?i";
		}

		[$where_clause, $args, $order_args] = self::_prepareArgsForLeftMenuQuery(
			$user_id, $filter_single, $filter_favorite, $filter_unread, $filter_unblocked, $filter_owner, $filter_system, $filter_support
		);

		if (mb_strlen(trim($search_query)) > 0) {

			$where_clause .= " AND `conversation_name` LIKE ?s";
			$args[]       = self::_prepareQuery($search_query);
		}

		$args  = array_merge($args, $order_args, [$count, $offset]);
		$query = $query . $where_clause . $order_by_clause;

		$left_menu_list = static::_connect(self::_getDbKey())->getAll($query, $table, ...$args);

		// добавляем последнее сообщение к результату
		foreach ($left_menu_list as $k => $v) {
			$left_menu_list[$k]["last_message"] = fromJson($v["last_message"]);
		}

		return $left_menu_list;
	}

	/**
	 * Подготовить параметры для запроса
	 *
	 * @param int $user_id
	 * @param int $filter_single
	 * @param int $filter_favorite
	 * @param int $filter_unread
	 * @param int $filter_unblocked
	 * @param int $filter_owner
	 * @param int $filter_system
	 *
	 * @return array
	 * @long
	 */
	protected static function _prepareArgsForLeftMenuQuery(int $user_id, int $filter_single, int $filter_favorite, int $filter_unread, int $filter_unblocked, int $filter_owner, int $filter_system, int $filter_support):array {

		$where_clause = "";
		$args         = [$user_id, 0];

		// если установили флаг "только непрочитанные"
		$order_args = [];
		if ($filter_unread) {

			$args[] = 1;
		}

		// если стоит фильтр на избранных
		if (in_array($filter_favorite, [-1, 1])) {

			$where_clause .= " AND `is_favorite` = ?i";
			$args[]       = match ($filter_favorite) {
				-1 => 0,
				1  => 1,
			};
		}

		if ($filter_owner && in_array($filter_single, [0, -1])) {

			if ($filter_system && $filter_single == 0) {

				// если установлен флаг "вернуть все чаты", кроме системных.
				$where_clause .= " AND ((`role` = ?i AND `type` IN (?a)) OR `type` = ?i)";
				$args         = array_merge($args, [
					Type_Conversation_Meta_Users::ROLE_OWNER,
					[CONVERSATION_TYPE_GROUP_DEFAULT, CONVERSATION_TYPE_GROUP_RESPECT, CONVERSATION_TYPE_GROUP_GENERAL], CONVERSATION_TYPE_SINGLE_DEFAULT,
				]);
			} elseif ($filter_system && $filter_single == -1) {

				// если установлен флаг "вернуть только группы, где мы админ", кроме системных чатов.
				$where_clause .= " AND ((`role` = ?i AND `type` IN (?a)))";
				$args         = array_merge($args, [
					Type_Conversation_Meta_Users::ROLE_OWNER,
					[CONVERSATION_TYPE_GROUP_DEFAULT, CONVERSATION_TYPE_GROUP_RESPECT, CONVERSATION_TYPE_GROUP_GENERAL],
				]);
			} else {

				// вернуть все чаты, включая системные
				$where_clause .= " AND (`role` = ?i OR `type` = ?i)";
				$args         = array_merge($args, [Type_Conversation_Meta_Users::ROLE_OWNER, CONVERSATION_TYPE_SINGLE_DEFAULT]);
			}
		}

		// если установлен флаг "вернуть только незаблокированных"
		if ($filter_unblocked && $filter_single >= 0) {

			switch ($filter_single) {

				case 1:
					$where_clause .= " AND `allow_status_alias` = ?i";
					$args[]       = Type_Conversation_Utils::ALLOW_STATUS_OK;
					break;
				case 0:
					$where_clause .= " AND `allow_status_alias` IN (?a)";
					$args[]       = [0, Type_Conversation_Utils::ALLOW_STATUS_OK, 0];
			}
		}

		// если установлен фильтр "только синглы"
		if ($filter_single == 1) {

			$where_clause .= " AND `type` = ?i";
			$args[]       = CONVERSATION_TYPE_SINGLE_DEFAULT;
		}

		if ($filter_owner == 0 && $filter_single == -1) {

			$where_clause .= " AND `type` IN (?a)";
			if ($filter_support == 1) {
				$args[] = [CONVERSATION_TYPE_GROUP_DEFAULT, CONVERSATION_TYPE_GROUP_RESPECT, CONVERSATION_TYPE_GROUP_GENERAL, CONVERSATION_TYPE_GROUP_SUPPORT];
			} else {
				$args[] = [CONVERSATION_TYPE_GROUP_DEFAULT, CONVERSATION_TYPE_GROUP_RESPECT, CONVERSATION_TYPE_GROUP_GENERAL];
			}
		}

		// если не нужно отдавать группу support. Не работает если есть флаг "только системны", если не нужно возвращать только синглы/не синглы и есть флаг только админ
		if (!$filter_support && !$filter_system && $filter_single == 0 && !$filter_owner) {

			$where_clause .= " AND `type` != ?i";
			$args[]       = CONVERSATION_TYPE_GROUP_SUPPORT;
		}

		// если установлен флаг "убрать системные чаты". Не работает, если выбран флаг только синглы. Если установлен флаг только админ - флаг применяется там
		if ($filter_system && $filter_single == 0 && !$filter_owner) {

			$where_clause .= " AND `type` NOT IN (?a)";
			if ($filter_support == 1) {
				$args[] = [CONVERSATION_TYPE_GROUP_HIRING, CONVERSATION_TYPE_SINGLE_NOTES, CONVERSATION_TYPE_SINGLE_WITH_SYSTEM_BOT];
			} else {
				$args[] = [CONVERSATION_TYPE_GROUP_HIRING, CONVERSATION_TYPE_SINGLE_NOTES, CONVERSATION_TYPE_SINGLE_WITH_SYSTEM_BOT, CONVERSATION_TYPE_GROUP_SUPPORT];
			}
		}

		// если был запрошен чат Поддержки, то добавляем аргумент для сортировки
		if ($filter_support == 1) {
			$order_args[] = CONVERSATION_TYPE_GROUP_SUPPORT;
		}

		return [$where_clause, $args, $order_args];
	}

	/**
	 * Установить имя сингл диалогов, для переданных пользователей, оппонентом которых является переданный пользователь
	 *
	 * @param array  $update_user_id_list
	 * @param int    $opponent_user_id
	 * @param string $name
	 * @param int    $limit
	 *
	 * @return void
	 */
	public static function setNameByOpponentIdInSingle(array $update_user_id_list, int $opponent_user_id, string $name, int $limit):void {

		$table = self::_getTable();

		$set = [
			"conversation_name" => $name,
		];

		// запрос проверен на EXPLAIN (INDEX=get_opponents)
		$query = "UPDATE `?p` FORCE INDEX (`get_opponents`) SET ?u WHERE `user_id` IN (?a) AND `opponent_user_id` = ?i LIMIT ?i";
		static::_connect(self::_getDbKey())->update($query, $table, $set, $update_user_id_list, $opponent_user_id, $limit);
	}

	/**
	 * Получаем сингл диалоги пользователя
	 *
	 * @param int $user_id
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return array
	 */
	public static function getSingleListByUserId(int $user_id, int $limit, int $offset):array {

		$table = self::_getTable();

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` FORCE INDEX (`PRIMARY`) WHERE `user_id` = ?i AND `type` = ?i LIMIT ?i OFFSET ?i";

		$left_menu_list = static::_connect(self::_getDbKey())->getAll($query, $table, $user_id, CONVERSATION_TYPE_SINGLE_DEFAULT, $limit, $offset);

		// добавляем последнее сообщение к результату
		foreach ($left_menu_list as $k => $v) {
			$left_menu_list[$k]["last_message"] = fromJson($v["last_message"]);
		}

		return $left_menu_list;
	}

	// получение диалога с пользователем
	public static function getByOpponentId(int $user_id, int $opponent_user_id):array {

		$table = self::_getTable();

		// запрос проверен на EXPLAIN (INDEX=get_opponents)
		$query         = "SELECT * FROM `?p` USE INDEX (`get_opponents`) WHERE `user_id`=?i AND `opponent_user_id`=?i AND `is_hidden`=?i LIMIT ?i";
		$left_menu_row = static::_connect(self::_getDbKey())->getOne($query, $table, $user_id, $opponent_user_id, 0, 1);

		// если пришел существующий left_menu для user_id - отдаем последнее сообщение
		if (isset($left_menu_row["user_id"])) {
			$left_menu_row["last_message"] = fromJson($left_menu_row["last_message"]);
		}

		return $left_menu_row;
	}

	// получить доступных собеседников
	public static function getAllowOpponents(int $user_id, int $offset, int $limit):array {

		$table = self::_getTable();

		// запрос проверен на EXPLAIN(INDEX=get_allowed)
		$query = "SELECT * FROM `?p` USE INDEX (`get_allowed`) WHERE `user_id` =?i AND `is_hidden` =?i AND `allow_status_alias` = ?i AND `type` IN (?a) ORDER BY `is_favorite` DESC, `is_mentioned` DESC, `updated_at` DESC LIMIT ?i OFFSET ?i";

		$left_menu_list = static::_connect(self::_getDbKey())->getAll($query, $table, $user_id, 0, Type_Conversation_Utils::ALLOW_STATUS_OK, Type_Conversation_Meta::getAllowedSingleSubtypes(), $limit, $offset);

		// добавляем последнее сообщение к результату
		foreach ($left_menu_list as $k => $v) {
			$left_menu_list[$k]["last_message"] = fromJson($v["last_message"]);
		}
		return $left_menu_list;
	}

	// получаем доступных собседников опираясь на запрос из sphinx
	public static function getAllowByConversationMapList(int $user_id, array $conversation_map_list):array {

		$table = self::_getTable();

		// запрос проверен на EXPLAIN(INDEX=get_allowed)
		$query = "SELECT * FROM `?p` USE INDEX (`get_allowed`) WHERE `user_id` =?i AND `is_hidden` =?i AND `allow_status_alias` = ?i AND `type` IN (?a) AND `conversation_map` IN (?a) ORDER BY `is_favorite` DESC, `updated_at` DESC LIMIT ?i";

		$left_menu_list = static::_connect(self::_getDbKey())->getAll($query, $table, $user_id, 0, Type_Conversation_Utils::ALLOW_STATUS_OK, Type_Conversation_Meta::getAllowedSingleSubtypes(), $conversation_map_list, count($conversation_map_list));

		// добавляем последнее сообщение к результату
		foreach ($left_menu_list as $k => $v) {
			$left_menu_list[$k]["last_message"] = fromJson($v["last_message"]);
		}
		return $left_menu_list;
	}

	// получить всех кроме удаленных собеседников
	public static function getAllowAndBlockedOpponents(int $user_id, int $offset, int $limit):array {

		$table = self::_getTable();

		// запрос проверен на EXPLAIN(INDEX=get_allowed)
		$query          = "SELECT * FROM `?p` USE INDEX (`get_allowed`) WHERE `user_id` =?i AND `is_hidden` =?i AND `allow_status_alias` != ?i AND `type` IN (?a) ORDER BY `is_favorite` DESC, `updated_at` DESC LIMIT ?i OFFSET ?i";
		$left_menu_list = static::_connect(self::_getDbKey())->getAll($query, $table, $user_id, 0, Type_Conversation_Utils::ALLOW_STATUS_MEMBER_IS_DISABLED, Type_Conversation_Meta::getAllowedSingleSubtypes(), $limit, $offset);

		// добавляем последнее сообщение к результату
		foreach ($left_menu_list as $k => $v) {
			$left_menu_list[$k]["last_message"] = fromJson($v["last_message"]);
		}
		return $left_menu_list;
	}

	// получаем не удаленных собседников опираясь на запрос из sphinx
	public static function getAllowAndBlockedByConversationMapList(int $user_id, array $conversation_map_list):array {

		$table = self::_getTable();

		// запрос проверен на EXPLAIN(INDEX=get_allowed)
		$query          = "SELECT * FROM `?p` USE INDEX (`get_allowed`) WHERE `user_id` =?i AND `is_hidden` =?i AND `allow_status_alias` != ?i AND `type` IN (?a) AND `conversation_map` IN (?a) ORDER BY `is_favorite` DESC, `updated_at` DESC LIMIT ?i";
		$left_menu_list = static::_connect(self::_getDbKey())->getAll($query, $table, $user_id, 0, Type_Conversation_Utils::ALLOW_STATUS_MEMBER_IS_DISABLED, Type_Conversation_Meta::getAllowedSingleSubtypes(), $conversation_map_list, count($conversation_map_list));

		// добавляем последнее сообщение к результату
		foreach ($left_menu_list as $k => $v) {
			$left_menu_list[$k]["last_message"] = fromJson($v["last_message"]);
		}
		return $left_menu_list;
	}

	// получение избранных диалогов из left_menu (при этом is_leaved = 0 & is_hidden = 0)
	public static function getLeftMenuFavorites(int $user_id, int $limit):array {

		$table = self::_getTable();

		// проверил на EXPLAIN (index=get_left_menu)
		$query          = "SELECT * FROM `?p` USE INDEX (`?p`) WHERE user_id = ?i AND `is_favorite` = ?i AND `is_hidden` = ?i AND `is_leaved` = ?i ORDER BY `is_mentioned` DESC, `updated_at` DESC LIMIT ?i";
		$left_menu_list = static::_connect(self::_getDbKey())->getAll($query, $table, self::_LEFT_MENU_INDEX_NAME, $user_id, 1, 0, 0, $limit);

		$output = [];

		foreach ($left_menu_list as $row) {

			$row["last_message"] = fromJson($row["last_message"]);
			$output[]            = $row;
		}

		return $output;
	}

	// получение количества избранных диалогов пользователя
	public static function getFavoritesCount(int $user_id):int {

		$table = self::_getTable();

		$query = "SELECT COUNT(*) AS `count` FROM `?p` WHERE user_id = ?i AND `is_favorite` = ?i LIMIT ?i";
		return static::_connect(self::_getDbKey())->getOne($query, $table, $user_id, 1, 1)["count"];
	}

	// получение общего числа непрочитанных сообщений
	public static function getTotalUnreadCounters(int $user_id):array {

		$table = self::_getTable();

		// запрос проверен на EXPLAIN (INDEX=`get_unread_menu`)
		$query = "SELECT SUM(`unread_count`) AS message_unread_count, COUNT(`conversation_map`) AS conversation_unread_count, COUNT(CASE WHEN `type` IN (?a) THEN TRUE END) AS single_conversation_unread_count FROM `?p` USE INDEX(`get_unread_menu`) WHERE `user_id` = ?i AND `is_hidden` = ?i AND `is_have_notice` = ?i LIMIT ?i";
		return static::_connect(self::_getDbKey())->getOne($query, $table, Type_Conversation_Meta::getSingleSubtypes(), $user_id, 0, 1, 1);
	}

	// получение групповых диалогов, где пользователь имеет админ права
	public static function getListWhereRole(int $user_id, int $count, int $offset, array $role_in):array {

		$table = self::_getTable();

		$query          = "SELECT * FROM `?p` WHERE user_id = ?i AND `role` IN (?a) ORDER BY `updated_at` DESC LIMIT ?i OFFSET ?i";
		$left_menu_list = static::_connect(self::_getDbKey())->getAll($query, $table, $user_id, $role_in, $count, $offset);

		// формируем ответ
		$output = [];
		foreach ($left_menu_list as $item) {

			$item["last_message"] = fromJson($item["last_message"]);
			$output[]             = $item;
		}

		return $output;
	}

	// получение групповых диалогов, где пользователь имеет админ права, кроме переданных типов
	public static function getListWhereRoleWithoutType(int $user_id, int $count, int $offset, array $role_in, array $without_type):array {

		$table = self::_getTable();

		$query          = "SELECT * FROM `?p` WHERE user_id = ?i AND `role` IN (?a) AND `type` NOT IN (?a)  ORDER BY `updated_at` DESC LIMIT ?i OFFSET ?i";
		$left_menu_list = static::_connect(self::_getDbKey())->getAll($query, $table, $user_id, $role_in, $without_type, $count, $offset);

		// формируем ответ
		$output = [];
		foreach ($left_menu_list as $item) {

			$item["last_message"] = fromJson($item["last_message"]);
			$output[]             = $item;
		}

		return $output;
	}

	// получение групповых диалогов из массива ключей, где пользователь имеет одну из указанных ролей
	public static function getListWhereRoleByMapList(int $user_id, array $conversation_map_list, array $role_in):array {

		$table = self::_getTable();

		// проверил запрос на EXPLAIN (key=PRIMARY)
		$count_limit    = count($conversation_map_list);
		$query          = "SELECT * FROM `?p` WHERE user_id = ?i AND `role` IN (?a) AND `conversation_map` IN (?a) ORDER BY `updated_at` DESC LIMIT ?i";
		$left_menu_list = static::_connect(self::_getDbKey())->getAll($query, $table, $user_id, $role_in, $conversation_map_list, $count_limit);

		// формируем ответ
		$output = [];
		foreach ($left_menu_list as $item) {

			$item["last_message"] = fromJson($item["last_message"]);
			$output[]             = $item;
		}

		return $output;
	}

	// получение список диалогов по роли и типу
	public static function getListWhereRoleAndType(int $user_id, int $count, int $offset, array $role_in, array $type_in):array {

		$table = self::_getTable();

		// запрос проверен на EXPLAIN(INDEX=get_managed)
		$query          = "SELECT * FROM `?p` USE INDEX (`get_managed`) WHERE user_id = ?i AND `role` IN (?a) AND `type` IN (?a) ORDER BY `updated_at` DESC LIMIT ?i OFFSET ?i";
		$left_menu_list = static::_connect(self::_getDbKey())->getAll($query, $table, $user_id, $role_in, $type_in, $count, $offset);

		// формируем ответ
		$output = [];
		foreach ($left_menu_list as $item) {

			$item["last_message"] = fromJson($item["last_message"]);
			$output[]             = $item;
		}

		return $output;
	}

	/**
	 * Возвращает список элементов левого меню, доступных пользователю.
	 */
	public static function getAllowedListByConversationMap(int $user_id, array $conversation_map_list):array {

		// EXPLAIN: INDEX PRIMARY
		$query          = "SELECT * FROM `?p` WHERE user_id = ?i AND `conversation_map` IN (?a) AND `is_hidden` = ?i AND `is_leaved` = ?i LIMIT ?i";
		$left_menu_list = static::_connect(self::_getDbKey())->getAll($query, self::_getTable(), $user_id, $conversation_map_list, 0, 0, count($conversation_map_list));

		foreach ($left_menu_list as $k => $v) {
			$left_menu_list[$k]["last_message"] = fromJson($v["last_message"]);
		}

		return $left_menu_list;
	}

	// -------------------------------------------------------
	// методы для работы с версионностью поля last_message
	// -------------------------------------------------------

	// текущая версия
	protected const _CURRENT_LAST_MESSAGE_VERSION = 2;

	// схема last_message
	protected const _LAST_MESSAGE_SCHEMA_LIST = [

		1 => [
			"message_map"                => "",
			"sender_user_id"             => 0,
			"conversation_message_index" => 0,
			"created_at"                 => 0,
			"type"                       => 0,
			"text"                       => "",
			"file_map"                   => "",
			"call_map"                   => "",
			"invite_map"                 => "",
			"is_hidden"                  => false,
			"file_name"                  => "",
			"message_count"              => 0,
			"receiver_id"                => 0,
			"additional_type"            => "",
		],
		2 => [
			"message_map"                => "",
			"sender_user_id"             => 0,
			"conversation_message_index" => 0,
			"created_at"                 => 0,
			"type"                       => 0,
			"text"                       => "",
			"file_map"                   => "",
			"call_map"                   => "",
			"invite_map"                 => "",
			"is_hidden"                  => false,
			"file_name"                  => "",
			"message_count"              => 0,
			"receiver_id"                => 0,
			"additional_type"            => "",
			"data"                       => [
				"conference_id"            => "",
				"conference_accept_status" => "",
			],
		],
	];

	// создать новую структуру для last_message
	public static function initLastMessage(string $message_map, int $sender_user_id, int $conversation_message_index,
							   int    $message_created_at, int $message_type, string $message_text,
							   string $file_map = "", string $call_map = "", string $invite_map = "", string $file_name = "", int $message_count = 0,
							   int    $receiver_id = 0, string $additional_type = "", string $conference_id = "", string $conference_accept_status = ""):array {

		// получаем текущую схему last_message
		$last_message_schema = self::_LAST_MESSAGE_SCHEMA_LIST[self::_CURRENT_LAST_MESSAGE_VERSION];

		// устанавливаем параметры
		$last_message_schema["message_map"]                      = $message_map;
		$last_message_schema["sender_user_id"]                   = $sender_user_id;
		$last_message_schema["conversation_message_index"]       = $conversation_message_index;
		$last_message_schema["created_at"]                       = $message_created_at;
		$last_message_schema["type"]                             = $message_type;
		$last_message_schema["text"]                             = $message_text;
		$last_message_schema["file_map"]                         = $file_map;
		$last_message_schema["call_map"]                         = $call_map;
		$last_message_schema["invite_map"]                       = $invite_map;
		$last_message_schema["file_name"]                        = $file_name;
		$last_message_schema["message_count"]                    = $message_count;
		$last_message_schema["receiver_id"]                      = $receiver_id;
		$last_message_schema["additional_type"]                  = $additional_type;
		$last_message_schema["data"]["conference_id"]            = $conference_id;
		$last_message_schema["data"]["conference_accept_status"] = $conference_accept_status;

		// устанавливаем текущую версию
		$last_message_schema["version"] = self::_CURRENT_LAST_MESSAGE_VERSION;

		return $last_message_schema;
	}

	// создать новую структуру для скрытого last_message
	public static function initHiddenLastMessage(int $conversation_message_index):array {

		// получаем текущую схему last_message
		$last_message_schema = self::_LAST_MESSAGE_SCHEMA_LIST[self::_CURRENT_LAST_MESSAGE_VERSION];

		// устанавливаем параметры
		$last_message_schema["conversation_message_index"] = $conversation_message_index;
		$last_message_schema["is_hidden"]                  = true;

		// устанавливаем текущую версию
		$last_message_schema["version"] = self::_CURRENT_LAST_MESSAGE_VERSION;

		return $last_message_schema;
	}

	// получить conversation_message_index из last_message
	public static function getConversationMessageIndex(array $last_message):int {

		$last_message = self::_getLastMessageSchema($last_message);

		return $last_message["conversation_message_index"];
	}

	// получить is_hidden из last_message
	public static function isHidden(array $last_message):bool {

		$last_message = self::_getLastMessageSchema($last_message);

		return $last_message["is_hidden"];
	}

	// получить message_map
	public static function getLastMessageMap(array $last_message):string {

		$last_message = self::_getLastMessageSchema($last_message);

		return $last_message["message_map"];
	}

	// получить sender_id
	public static function getLastMessageSenderUserId(array $last_message):int {

		$last_message = self::_getLastMessageSchema($last_message);

		return $last_message["sender_user_id"];
	}

	// получить type
	public static function getLastMessageType(array $last_message):int {

		$last_message = self::_getLastMessageSchema($last_message);

		return $last_message["type"];
	}

	// получить text
	public static function getLastMessageText(array $last_message):string {

		$last_message = self::_getLastMessageSchema($last_message);

		return $last_message["text"];
	}

	// получить file_map
	public static function getLastMessageFileMap(array $last_message):string {

		$last_message = self::_getLastMessageSchema($last_message);

		return $last_message["file_map"];
	}

	// получить file_name
	public static function getLastMessageFileName(array $last_message):string {

		$last_message = self::_getLastMessageSchema($last_message);

		return $last_message["file_name"];
	}

	// получить call_map
	public static function getLastMessageCallMap(array $last_message):string {

		$last_message = self::_getLastMessageSchema($last_message);

		return $last_message["call_map"];
	}

	// получить conference_id
	public static function getLastMessageConferenceId(array $last_message):string {

		$last_message = self::_getLastMessageSchema($last_message);

		return $last_message["data"]["conference_id"];
	}

	// получить conference_status
	public static function getLastMessageConferenceStatus(array $last_message):string {

		$last_message = self::_getLastMessageSchema($last_message);

		return $last_message["data"]["conference_accept_status"];
	}

	// получить invite_map
	public static function getLastMessageInviteMap(array $last_message):string {

		$last_message = self::_getLastMessageSchema($last_message);

		return $last_message["invite_map"];
	}

	// получить количество репостнутых/процитированных сообщений
	public static function getLastMessageMessageCountIfRepostOrQuote(array $last_message):string {

		$last_message = self::_getLastMessageSchema($last_message);

		return $last_message["message_count"];
	}

	// получить имя пользователя, кому выдали сообщение с additional-полями
	public static function getLastMessageAdditionalReceiverId(array $last_message):int {

		$last_message = self::_getLastMessageSchema($last_message);

		return $last_message["receiver_id"];
	}

	// получить тип сообщения, имеющее additional-поля
	public static function getLastMessageAdditionalType(array $last_message):string {

		$last_message = self::_getLastMessageSchema($last_message);

		return $last_message["additional_type"];
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// получить актуальную структуру для last_message
	protected static function _getLastMessageSchema(array $last_message_schema):array {

		if (!isset($last_message_schema["version"])) {

			$last_message_schema            = self::_LAST_MESSAGE_SCHEMA_LIST[self::_CURRENT_LAST_MESSAGE_VERSION];
			$last_message_schema["version"] = self::_CURRENT_LAST_MESSAGE_VERSION;

			return $last_message_schema;
		}

		// если версия не совпадает - дополняем её до текущей
		if ($last_message_schema["version"] != self::_CURRENT_LAST_MESSAGE_VERSION) {

			// получаем текущую схему last_message
			$current_last_message_schema = self::_LAST_MESSAGE_SCHEMA_LIST[self::_CURRENT_LAST_MESSAGE_VERSION];

			$last_message_schema            = array_merge($current_last_message_schema, $last_message_schema);
			$last_message_schema["version"] = self::_CURRENT_LAST_MESSAGE_VERSION;
		}

		return $last_message_schema;
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// получаем таблицу
	protected static function _getTable():string {

		return self::_TABLE_KEY;
	}

	/**
	 * Подготовить запрос к LIKE
	 *
	 * @param string $search_query
	 *
	 * @return string
	 */
	protected static function _prepareQuery(string $search_query):string {

		$search_query = \Entity_Sanitizer::sanitizeUtf8Query($search_query);

		// экранируем символы mysql
		$search_query = str_replace("_", "\_", $search_query);
		$search_query = str_replace("%", "\%", $search_query);
		return "%$search_query%";
	}
}