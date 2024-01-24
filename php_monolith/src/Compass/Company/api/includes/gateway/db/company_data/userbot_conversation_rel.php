<?php

namespace Compass\Company;

/**
 * Класс-интерфейс для таблицы company_data.userbot_conversation_rel
 */
class Gateway_Db_CompanyData_UserbotConversationRel extends Gateway_Db_CompanyData_Main {

	protected const _TABLE_KEY = "userbot_conversation_rel";

	/**
	 * создание записи
	 */
	public static function insertList(array $userbot_id_list, int $conversation_type, string $conversation_map):void {

		$insert_list = [];
		foreach ($userbot_id_list as $userbot_id) {

			$insert_list[] = [
				"userbot_id"        => $userbot_id,
				"conversation_type" => $conversation_type,
				"created_at"        => time(),
				"conversation_map"  => $conversation_map,
			];
		}

		ShardingGateway::database(self::_DB_KEY)->insertArray(self::_TABLE_KEY, $insert_list);
	}

	/**
	 * получаем записи для бота
	 */
	public static function getByUserbotId(string $userbot_id):array {

		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE TRUE LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, 1);
		$count = $row["count"];

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`get_by_userbot_id_and_conversation`)
		$query = "SELECT * FROM `?p` WHERE `userbot_id` = ?s LIMIT ?i";
		$list  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $userbot_id, $count);

		$obj_list = [];
		foreach ($list as $row) {
			$obj_list[] = self::_rowToObject($row);
		}

		return $obj_list;
	}

	/**
	 * получаем записи для списка ботов
	 */
	public static function getByUserbotList(array $userbot_id_list):array {

		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE TRUE LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, 1);
		$count = $row["count"];

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`get_by_userbot_id_and_conversation`)
		$query = "SELECT * FROM `?p` WHERE `userbot_id` IN (?a) LIMIT ?i";
		$list  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $userbot_id_list, $count);

		$obj_list = [];
		foreach ($list as $row) {
			$obj_list[] = self::_rowToObject($row);
		}

		return $obj_list;
	}

	/**
	 * получаем записи по мапе диалога
	 */
	public static function getListByConversationMap(array $userbot_id_list, string $conversation_map):array {

		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE TRUE LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, 1);
		$count = $row["count"];

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`get_by_userbot_id_and_conversation`)
		$query = "SELECT * FROM `?p` WHERE `userbot_id` IN (?a) AND `conversation_map` = ?s LIMIT ?i";
		$list  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $userbot_id_list, $conversation_map, $count);

		$obj_list = [];
		foreach ($list as $row) {
			$obj_list[] = self::_rowToObject($row);
		}

		return $obj_list;
	}

	/**
	 * удаляем группу у бота
	 */
	public static function delete(string $userbot_id, string $conversation_map):void {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`get_by_userbot_id_and_conversation`)
		$query = "DELETE FROM `?p` WHERE `userbot_id` = ?s AND `conversation_map` = ?s LIMIT ?i";
		ShardingGateway::database(self::_DB_KEY)->delete($query, self::_TABLE_KEY, $userbot_id, $conversation_map, 1);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * превращаем строку в объект
	 */
	protected static function _rowToObject(array $row):Struct_Db_CloudCompany_UserbotConversationRel {

		return new Struct_Db_CloudCompany_UserbotConversationRel(
			$row["row_id"],
			$row["userbot_id"],
			$row["conversation_type"],
			$row["created_at"],
			$row["conversation_map"]
		);
	}
}