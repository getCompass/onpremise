<?php

namespace Compass\Company;

/**
 * Класс-интерфейс для таблицы company_data.userbot_conversation_history
 */
class Gateway_Db_CompanyData_UserbotConversationHistory extends Gateway_Db_CompanyData_Main {

	protected const _TABLE_KEY = "userbot_conversation_history";

	/**
	 * создание записи
	 */
	public static function insertList(array $userbot_id_list, int $action_type, string $conversation_map):void {

		$insert_list = [];
		foreach ($userbot_id_list as $userbot_id) {

			$insert_list[] = [
				"userbot_id"       => $userbot_id,
				"action_type"      => $action_type,
				"created_at"       => time(),
				"updated_at"       => 0,
				"conversation_map" => $conversation_map,
			];
		}

		ShardingGateway::database(self::_DB_KEY)->insertArray(self::_TABLE_KEY, $insert_list);
	}

	/**
	 * получаем записи по мапе диалога
	 */
	public static function getListByConversationMap(array $userbot_id_list, string $conversation_map):array {

		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE TRUE LIMIT ?i;";
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

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * превращаем строку в объект
	 */
	protected static function _rowToObject(array $row):Struct_Db_CloudCompany_UserbotConversationHistory {

		return new Struct_Db_CloudCompany_UserbotConversationHistory(
			$row["row_id"],
			$row["userbot_id"],
			$row["action_type"],
			$row["created_at"],
			$row["updated_at"],
			$row["conversation_map"]
		);
	}
}