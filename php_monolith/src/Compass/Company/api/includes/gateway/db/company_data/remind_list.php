<?php

namespace Compass\Company;

/**
 * Класс-интерфейс для таблицы company_data . remind_list
 */
class Gateway_Db_CompanyData_RemindList extends Gateway_Db_CompanyData_Main {

	protected const _TABLE_KEY = "remind_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для обновления записи
	 *
	 * @throws \parseException
	 */
	public static function set(int $remind_id, array $set):void {

		// формируем и осуществляем запрос
		$query = "UPDATE `?p` SET ?u WHERE `remind_id` = ?i LIMIT ?i";
		ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_KEY, $set, $remind_id, 1);
	}

	/**
	 * получаем запись
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	public static function getOne(int $remind_id):Struct_Db_CompanyData_Remind {

		// формируем и осуществляем запрос
		$query = "SELECT * FROM `?p` WHERE `remind_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $remind_id, 1);

		if (!isset($row["remind_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_formatRow($row);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * преобразовываем массив в структуру
	 */
	protected static function _formatRow(array $row):Struct_Db_CompanyData_Remind {

		return new Struct_Db_CompanyData_Remind(
			$row["remind_id"],
			$row["is_done"],
			$row["type"],
			$row["remind_at"],
			$row["creator_user_id"],
			$row["created_at"],
			$row["updated_at"],
			$row["recipient_id"],
			fromJson($row["data"]),
		);
	}
}