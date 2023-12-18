<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс-интерфейс для таблицы company_data.member_menu
 */
class Gateway_Db_CompanyData_MemberMenu extends Gateway_Db_CompanyData_Main {

	protected const _TABLE_KEY = "member_menu";

	public const NOTIFICATION_LIMIT = 100000;

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Метод для вставки нескольких записей
	 *
	 * @param Struct_Db_CompanyData_MemberMenu[] $member_menu_list
	 *
	 * @return string
	 * @throws ParseFatalException
	 */
	public static function insertList(array $member_menu_list):string {

		$table_key = self::_getTableKey();
		$shard_key = self::_getDbKey();

		$insert_list = array_map(fn(Struct_Db_CompanyData_MemberMenu $v) => (array) $v, $member_menu_list);

		return ShardingGateway::database($shard_key)->insertArray($table_key, $insert_list);
	}

	/**
	 * Получаем записи по списку user_id
	 *
	 * @throws ParseFatalException
	 */
	public static function getNotificationList(array $user_id_list, int $action_user_id, int $type):array {

		$table_key = self::_getTableKey();
		$shard_key = self::_getDbKey();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=user_id.action_user_id.type)
		$query = "SELECT * FROM `?p` WHERE `user_id` IN (?a) AND `action_user_id` = ?i AND `type` = ?i LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_key, $user_id_list, $action_user_id, $type, self::NOTIFICATION_LIMIT);

		return self::_formatList($list);
	}

	/**
	 * Получаем записи по списку user_id
	 *
	 * @throws ParseFatalException
	 */
	public static function getUnreadNotificationList(array $user_id_list, int $action_user_id, int $type):array {

		$table_key = self::_getTableKey();
		$shard_key = self::_getDbKey();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=user_id.action_user_id.type)
		$query = "SELECT * FROM `?p` WHERE `user_id` IN (?a) AND `action_user_id` = ?i AND `type` = ?i AND `is_unread` = ?i LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_key, $user_id_list, $action_user_id, $type, 1, self::NOTIFICATION_LIMIT);

		return self::_formatList($list);
	}

	/**
	 * Получаем все непрочитанные уведомления
	 *
	 * @throws ParseFatalException
	 */
	public static function getAllUserUnreadNotifications(int $user_id):array {

		$table_key = self::_getTableKey();
		$shard_key = self::_getDbKey();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=user_id.is_unread.type)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `is_unread` = ?i LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_key, $user_id, 1, self::NOTIFICATION_LIMIT);

		return self::_formatList($list);
	}

	/**
	 * Получаем список записей по user_id и списку типов уведомлений
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	public static function getUnreadNotificationsByType(int $user_id, array $type_list):array {

		$table_key = self::_getTableKey();
		$shard_key = self::_getDbKey();

		// получаем записи, которые будем обновлять
		// запрос проверен на EXPLAIN (INDEX=user_id.is_unread.type)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `is_unread` = ?i AND `type` IN (?a) LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_key, $user_id, 1, $type_list, self::NOTIFICATION_LIMIT);

		return self::_formatList($list);
	}

	/**
	 * Обновляем список записей
	 *
	 * @return int
	 * @throws ParseFatalException
	 */
	public static function setList(array $notification_id_list, array $set):int {

		// если нет идентификаторов, то ничего не делаем
		if (count($notification_id_list) < 1) {
			return 0;
		}

		$table_key = self::_getTableKey();
		$shard_key = self::_getDbKey();

		// запрос проверен на EXPLAIN (PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `notification_id` IN (?a) LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_key, $set, $notification_id_list, count($notification_id_list));
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	/**
	 * Получить ключ таблицы
	 *
	 * @return string
	 */
	protected static function _getTableKey():string {

		return self::_TABLE_KEY;
	}

	/**
	 * Форматируем список записей
	 *
	 * @return array
	 */
	protected static function _formatList(array $list):array {

		$output = [];
		foreach ($list as $row) {
			$output[] = self::_formatRow($row);
		}

		return $output;
	}

	/**
	 * Форматируем запись
	 */
	protected static function _formatRow(array $row):Struct_Db_CompanyData_MemberMenu {

		return new Struct_Db_CompanyData_MemberMenu(
			(int) $row["notification_id"],
			(int) $row["user_id"],
			(int) $row["action_user_id"],
			(int) $row["type"],
			(int) $row["is_unread"],
			(int) $row["created_at"],
			(int) $row["updated_at"],
		);
	}
}