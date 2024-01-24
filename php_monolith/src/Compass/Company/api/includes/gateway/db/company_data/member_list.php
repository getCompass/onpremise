<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс-интерфейс для таблицы company_data.member_list
 */
class Gateway_Db_CompanyData_MemberList extends Gateway_Db_CompanyData_Main {

	protected const _TABLE_KEY         = "member_list";
	protected const _GET_BY_ROLE_INDEX = "npc_type.role.company_joined_at";
	public const    MAX_COUNT          = 10000;

	protected const _INDEX_BY_SORT_FIELD = [
		"company_joined_at" => "get_by_npc_type",
		"full_name"         => "npc_type.full_name",
	];

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для вставки записи
	 *
	 * @mixed @long
	 */
	public static function insertOrUpdate(
		int    $user_id,
		int    $role,
		int    $npc_type,
		int    $permissions,
		string $mbti_type,
		string $full_name,
		string $short_description,
		string $avatar_file_key,
		string $comment,
		array  $extra
	):string {

		$insert = [
			"user_id"              => $user_id,
			"role"                 => $role,
			"npc_type"             => $npc_type,
			"permissions"          => $permissions,
			"created_at"           => time(),
			"updated_at"           => 0,
			"company_joined_at"    => time(),
			"left_at"              => 0,
			"full_name_updated_at" => 0,
			"mbti_type"            => $mbti_type,
			"full_name"            => $full_name,
			"short_description"    => $short_description,
			"avatar_file_key"      => $avatar_file_key,
			"comment"              => $comment,
			"extra"                => $extra,
		];
		return ShardingGateway::database(self::_DB_KEY)->insertOrUpdate(self::_TABLE_KEY, $insert);
	}

	/**
	 * метод для обновления записи
	 *
	 * @throws \busException
	 * @throws \parseException
	 */
	public static function set(int $user_id, array $set):int {

		foreach ($set as $field => $_) {

			if (!property_exists(\CompassApp\Domain\Member\Struct\Main::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		$set["updated_at"] = time();

		// формируем и осуществляем запрос
		$query     = "UPDATE `?p` SET ?u WHERE `user_id` = ?i LIMIT ?i";
		$row_count = ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_KEY, $set, $user_id, 1);

		// очищаем кеш пользователя
		Gateway_Bus_CompanyCache::clearMemberCacheByUserId($user_id);

		return $row_count;
	}

	/**
	 * метод для обновления нескольких записей
	 *
	 * @param array $user_id_list
	 * @param array $set
	 *
	 * @return int
	 * @throws ParseFatalException
	 */
	public static function setList(array $user_id_list, array $set):int {

		foreach ($set as $field => $_) {

			if (!property_exists(\CompassApp\Domain\Member\Struct\Main::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		$set["updated_at"] = time();

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query     = "UPDATE `?p` SET ?u WHERE `user_id` IN (?a) LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_KEY, $set, $user_id_list, count($user_id_list));
	}

	/**
	 * метод для получения записи
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(int $user_id):\CompassApp\Domain\Member\Struct\Main {

		// формируем и осуществляем запрос
		$query = "SELECT * FROM `?p` WHERE `user_id`=?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $user_id, 1);
		if (!isset($row["user_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_formatRow($row);
	}

	/**
	 * метод для получения и блокировки записи
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getForUpdate(int $user_id):\CompassApp\Domain\Member\Struct\Main {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id`=?i LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $user_id, 1);
		if (!isset($row["user_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_formatRow($row);
	}

	/**
	 * Метод для получения списка участников по запросу (старый вариант, сортировка только по времени вступления)
	 *
	 * @param string $query
	 * @param int    $limit
	 * @param int    $offset
	 *
	 * @return int[]
	 * @throws ParseFatalException
	 */
	public static function getListByQueryLegacy(string $query, int $limit, int $offset, array $filter_npc_type, array $filter_role):array {

		$args         = [$filter_npc_type, $filter_role, $limit, $offset];
		$query_clause = "";

		if (mb_strlen(trim($query)) > 0) {

			$prepared_query = self::_prepareQuery($query);
			$query_clause   = " AND (`full_name` LIKE ?s OR `short_description` LIKE ?s)";
			$args           = [$filter_npc_type, $filter_role, $prepared_query, $prepared_query, $limit, $offset];
		}

		// запрос проверен на EXPLAIN (INDEX=get_by_npc_type)
		$query = "SELECT user_id FROM `?p` USE INDEX(`get_by_npc_type`) 
			WHERE `npc_type` IN (?a) AND `role` IN (?a) {$query_clause} 
			ORDER BY `company_joined_at` DESC LIMIT ?i OFFSET ?i";
		$rows  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, ...$args);
		$list  = [];

		foreach ($rows as $row) {
			$list[] = (int) $row["user_id"];
		}

		return $list;
	}

	/**
	 * Метод для получения списка участников по запросу
	 *
	 * @param string $query
	 * @param int    $limit
	 * @param int    $offset
	 *
	 * @return int[]
	 * @throws ParseFatalException
	 */
	public static function getListByQuery(string $query, int $limit, int $offset, array $filter_npc_type, array $filter_role, array $sort_fields):array {

		$args         = [$filter_npc_type, $filter_role, $limit, $offset];
		$query_clause = "";
		$sort_clause  = "ORDER BY ";

		if (mb_strlen(trim($query)) > 0) {

			$prepared_query = self::_prepareQuery($query);
			$query_clause   = " AND (`full_name` LIKE ?s OR `short_description` LIKE ?s)";
			$args           = [$filter_npc_type, $filter_role, $prepared_query, $prepared_query, $limit, $offset];
		}

		foreach ($sort_fields as $sort_field) {
			$sort_clause .= "`$sort_field` DESC, ";
		}

		$sort_clause = $sort_clause !== "ORDER BY " ? mb_substr($sort_clause, 0, -2) : "";

		// запрос проверен на EXPLAIN (INDEX=get_by_npc_type)
		$query = "SELECT user_id FROM `?p` USE INDEX(`get_by_npc_type`) 
			WHERE `npc_type` IN (?a) AND `role` IN (?a) {$query_clause} 
			{$sort_clause} LIMIT ?i OFFSET ?i";
		$rows  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, ...$args);
		$list  = [];

		foreach ($rows as $row) {
			$list[] = (int) $row["user_id"];
		}

		return $list;
	}

	/**
	 * метод для получения списка участников по маске прав
	 *
	 * @return \\CompassApp\Domain\Member\Struct\Main[]
	 * @throws ParseFatalException
	 */
	public static function getByPermissionMask(int $permissions, int $limit = 1):array {

		// запрос проверен на EXPLAIN (INDEX=npc_type.role.company_joined_at)
		$query = "SELECT * FROM `?p` FORCE INDEX(`?p`) WHERE `npc_type` = ?i AND `role` = ?i AND `permissions` & ?i ORDER BY `company_joined_at` ASC LIMIT ?i";
		$rows  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, self::_GET_BY_ROLE_INDEX, Type_User_Main::NPC_TYPE_HUMAN,
			\CompassApp\Domain\Member\Entity\Member::ROLE_ADMINISTRATOR, $permissions, $limit);

		$list = [];
		foreach ($rows as $row) {
			$list[$row["user_id"]] = self::_formatRow($row);
		}

		return $list;
	}

	/**
	 * метод для получения списка участников с точными правами
	 *
	 * @return \\CompassApp\Domain\Member\Struct\Main[]
	 * @throws ParseFatalException
	 */
	public static function getWithPermissions(int $permissions, int $limit = 1):array {

		// запрос проверен на EXPLAIN (INDEX=npc_type.role.company_joined_at)
		$query = "SELECT * FROM `?p` FORCE INDEX(`?p`) WHERE `npc_type` = ?i AND `role` = ?i AND `permissions` = ?i ORDER BY `company_joined_at` ASC LIMIT ?i";
		$rows  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, self::_GET_BY_ROLE_INDEX, Type_User_Main::NPC_TYPE_HUMAN,
			\CompassApp\Domain\Member\Entity\Member::ROLE_ADMINISTRATOR, $permissions, $limit);

		$list = [];
		foreach ($rows as $row) {
			$list[$row["user_id"]] = self::_formatRow($row);
		}

		return $list;
	}

	/**
	 * метод для получения списка участников по списку ролей
	 *
	 * @return \\CompassApp\Domain\Member\Struct\Main[]
	 */
	public static function getListByRoles(array $roles, int $limit = 1, int $offset = 0):array {

		// запрос проверен на EXPLAIN (INDEX=get_by_npc_type)
		$query = "SELECT * FROM `?p` FORCE INDEX(`?p`) WHERE `npc_type` = ?i AND `role` IN (?a) ORDER BY `company_joined_at` ASC LIMIT ?i OFFSET ?i";
		$rows  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, self::_GET_BY_ROLE_INDEX, Type_User_Main::NPC_TYPE_HUMAN,
			$roles, $limit, $offset);

		$list = [];
		foreach ($rows as $row) {
			$list[$row["user_id"]] = self::_formatRow($row);
		}

		return $list;
	}

	/**
	 * метод для получения все пользователей
	 *
	 * @return \\CompassApp\Domain\Member\Struct\Main[]
	 */
	public static function getAll():array {

		// ТОЛЬКО ДЛЯ СКРИПТОВ
		$query = "SELECT * FROM `?p` WHERE TRUE LIMIT ?i OFFSET ?i";
		$rows  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, 999999, 0);

		$list = [];
		foreach ($rows as $row) {
			$list[$row["user_id"]] = self::_formatRow($row);
		}

		return $list;
	}

	/**
	 * метод для получения списка участников у которых маска групп не совпадает
	 *
	 * @return \\CompassApp\Domain\Member\Struct\Main[]
	 */
	public static function getListNotInPermissionMask(int $permissions, int $limit = 1):array {

		//запрос проверен на EXPLAIN (INDEX=NULL)
		$query = "SELECT * FROM `?p` WHERE (`permissions` & ?i) = ?i LIMIT ?i";
		$rows  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $permissions, 0, $limit);

		$list = [];
		foreach ($rows as $row) {
			$list[] = self::_formatRow($row);
		}

		return $list;
	}

	/**
	 * метод для получения всех пользователей в компании
	 *
	 * @return \\CompassApp\Domain\Member\Struct\Main[]
	 */
	public static function getAllActiveMember(int $limit = 50, int $offset = 0):array {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE user_id > ?i AND `role` != ?i ORDER BY `created_at` ASC LIMIT ?i OFFSET ?i ";
		$rows  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, 0, \CompassApp\Domain\Member\Entity\Member::ROLE_LEFT,
			$limit, $offset);

		$list = [];

		foreach ($rows as $row) {
			$list[] = self::_formatRow($row);
		}

		return $list;
	}

	/**
	 * метод для получения всех пользователей в компании с фильтрацией по npc_type
	 *
	 * @return \\CompassApp\Domain\Member\Struct\Main[]
	 * @throws \parseException
	 */
	public static function getAllActiveMemberWithNpcFilter(int $limit = 50, int $offset = 0):array {

		// запрос проверен на EXPLAIN (INDEX=get_by_npc_type)
		$query = "SELECT * FROM `?p` WHERE `npc_type` = ?i AND user_id > ?i AND `role` != ?i ORDER BY `created_at` ASC LIMIT ?i OFFSET ?i ";
		$rows  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, Type_User_Main::NPC_TYPE_HUMAN, 0,
			\CompassApp\Domain\Member\Entity\Member::ROLE_LEFT, $limit, $offset);

		$list = [];

		foreach ($rows as $row) {
			$list[] = self::_formatRow($row);
		}

		return $list;
	}

	/**
	 * метод для получения всех забаненных пользователей
	 */
	public static function getKickedUserIdList():array {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT COUNT(*) AS count  FROM `?p` WHERE user_id > ?i AND `role` = ?i LIMIT ?i";
		$count = ShardingGateway::database(self::_DB_KEY)->getOne(
			$query, self::_TABLE_KEY, 0, \CompassApp\Domain\Member\Entity\Member::ROLE_LEFT, 1)["count"];

		$query = "SELECT * FROM `?p` WHERE user_id > ?i AND `role` = ?i LIMIT ?i";
		$list  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, 0, \CompassApp\Domain\Member\Entity\Member::ROLE_LEFT, $count);

		$output = [];
		foreach ($list as $user) {
			$output[] = $user["user_id"];
		}
		return $output;
	}

	/**
	 * Получает руководителя или владельца, который раньше всех вступил в компанию.
	 * Сортирует по ролям и дате вступления.
	 *
	 * @param array $role_list
	 * @param int   $permissions
	 *
	 * @return \CompassApp\Domain\Member\Struct\Main
	 * @throws ParseFatalException
	 * @throws cs_NoOwnerException
	 */
	public static function getOldestMember(array $role_list, int $permissions):\CompassApp\Domain\Member\Struct\Main {

		// запрос проверен на EXPLAIN (INDEX=npc_type.role.company_joined_at)
		$query = "SELECT * FROM `?p` FORCE INDEX(`?p`) WHERE `npc_type` = ?i AND role IN (?a) AND `permissions` & ?i ORDER BY role ASC, created_at ASC LIMIT ?i";
		$rows  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, self::_GET_BY_ROLE_INDEX,
			\CompassApp\Domain\User\Main::NPC_TYPE_HUMAN, $role_list, $permissions, 1);

		if (count($rows) === 0) {
			throw new cs_NoOwnerException("there is no active owners");
		}

		return self::_formatRow(reset($rows));
	}

	/**
	 * Получаем данные всех пользователей с БД с пагинацией
	 */
	public static function getAllActiveMemberWithPagination(int $offset, int $limit = self::MAX_COUNT):array {

		$indexed_list = [];

		// запрос проверен на EXPLAIN (INDEX=npc_type.role.company_joined_at)
		$query = "SELECT * FROM `?p` FORCE INDEX(`?p`) WHERE `npc_type` = ?i AND `role` != ?i LIMIT ?i OFFSET ?i";
		$list  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, self::_GET_BY_ROLE_INDEX,
			Type_User_Main::NPC_TYPE_HUMAN, \CompassApp\Domain\Member\Entity\Member::ROLE_LEFT, $limit, $offset);

		foreach ($list as $member) {
			$indexed_list[$member["user_id"]] = self::_formatRow($member);
		}
		$has_next = count($list) >= $limit;

		return [$indexed_list, $has_next];
	}

	/**
	 * Получаем данные всех пользователей с БД с пагинацией
	 */
	public static function getAllMemberWithPagination(int $offset, int $limit = self::MAX_COUNT):array {

		$indexed_list = [];

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE user_id > ?i LIMIT ?i OFFSET ?i";
		$list  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, 0, $limit, $offset);

		foreach ($list as $member) {
			$indexed_list[$member["user_id"]] = self::_formatRow($member);
		}
		$has_next = count($list) >= $limit;

		return [$indexed_list, $has_next];
	}

	/**
	 * Получаем список по mbti
	 *
	 * @param string $mbti_type
	 * @param array  $role_list
	 * @param int    $offset
	 * @param int    $count
	 *
	 * @return array
	 */
	public static function getListByMBTI(string $mbti_type, array $role_list, int $offset, int $count):array {

		// запрос проверен на EXPLAIN (INDEX=`mbti_type_by_role`)
		$query = "SELECT * FROM `?p` WHERE `mbti_type` = ?s AND role IN (?a) ORDER BY `company_joined_at` ASC LIMIT ?i OFFSET ?i";
		$rows  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $mbti_type, $role_list, $count, $offset);

		$output = [];
		foreach ($rows as $row) {
			$output[] = self::_formatRow($row);
		}
		return $output;
	}

	/**
	 * получаем участников по сортируемому полю
	 *
	 * @return \\CompassApp\Domain\Member\Struct\Main[]
	 * @throws ParseFatalException
	 */
	public static function getListBySortField(string $sort_field, string $sort_order, int $limit = 500, int $offset = 0):array {

		$index = self::_INDEX_BY_SORT_FIELD[$sort_field];

		$role_list = [
			\CompassApp\Domain\Member\Entity\Member::ROLE_MEMBER,
			\CompassApp\Domain\Member\Entity\Member::ROLE_ADMINISTRATOR,
		];

		// запрос проверен на EXPLAIN (INDEX=`npc_type.full_name` or `get_by_npc_type`)
		$query = "SELECT * FROM `?p` FORCE INDEX(`{$index}`) WHERE `npc_type` = ?i AND `role` IN (?a) ORDER BY {$sort_field} {$sort_order} LIMIT ?i OFFSET ?i";
		$rows  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, Type_User_Main::NPC_TYPE_HUMAN, $role_list, $limit, $offset);

		$list = [];
		foreach ($rows as $row) {
			$list[$row["user_id"]] = self::_formatRow($row);
		}

		return $list;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * преобразовываем массив в структуру
	 */
	protected static function _formatRow(array $row):\CompassApp\Domain\Member\Struct\Main {

		return new \CompassApp\Domain\Member\Struct\Main(
			$row["user_id"],
			$row["role"],
			$row["npc_type"],
			$row["permissions"],
			$row["created_at"],
			$row["updated_at"],
			$row["company_joined_at"],
			$row["left_at"],
			$row["full_name_updated_at"],
			$row["full_name"],
			$row["mbti_type"],
			$row["short_description"],
			$row["avatar_file_key"],
			$row["comment"],
			fromJson($row["extra"]),
		);
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