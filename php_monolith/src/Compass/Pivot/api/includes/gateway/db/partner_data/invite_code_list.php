<?php

namespace Compass\Pivot;

/**
 * Класс-интерфейс для таблицы partner_data . invite_code_list
 */
class Gateway_Db_PartnerData_InviteCodeList extends Gateway_Db_PartnerData_Main {

	protected const _TABLE_NAME = "invite_code_list";

	/**
	 * Создаем новую запись или обновляем существующую
	 */
	public static function insertOrUpdate(Struct_Db_PartnerData_InviteCode $invite_code):void {

		$insert_array = [
			"invite_code_hash" => $invite_code->invite_code_hash,
			"invite_code"      => $invite_code->invite_code,
			"partner_id"       => $invite_code->partner_id,
			"discount"         => $invite_code->discount,
			"can_reuse_after"  => $invite_code->can_reuse_after,
			"expires_at"       => $invite_code->expires_at,
			"created_at"       => time(),
			"updated_at"       => 0,
		];
		$update_array = [
			"invite_code_hash" => $invite_code->invite_code_hash,
			"invite_code"      => $invite_code->invite_code,
			"partner_id"       => $invite_code->partner_id,
			"discount"         => $invite_code->discount,
			"can_reuse_after"  => $invite_code->can_reuse_after,
			"expires_at"       => $invite_code->expires_at,
			"updated_at"       => time(),
		];
		ShardingGateway::database(self::_DB_KEY)->insertOrUpdate(self::_TABLE_NAME, $insert_array, $update_array);
	}

	/**
	 * Метод для получения записи компании
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(string $invite_code):Struct_Db_PartnerData_InviteCode {

		$db_key     = self::_getDbKey();
		$table_name = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=invite_code)
		$query = "SELECT * FROM `?p` WHERE `invite_code` = ?s LIMIT ?i";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_name, $invite_code, 1);
		if (!isset($row["invite_code_hash"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToObject($row);
	}

	/**
	 * Обновляем запись
	 */
	public static function set(string $invite_code_hash, array $set):void {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `invite_code_hash` = ?s LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->update($query, self::_TABLE_NAME, $set, $invite_code_hash, 1);
	}

	/**
	 * Удаляем запись
	 */
	public static function delete(string $invite_code_hash):void {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "DELETE FROM `?p` WHERE `invite_code_hash` = ?s LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->update($query, self::_TABLE_NAME, $invite_code_hash, 1);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Создаем структуру из строки бд
	 */
	protected static function _rowToObject(array $row):Struct_Db_PartnerData_InviteCode {

		return new Struct_Db_PartnerData_InviteCode(
			$row["invite_code_hash"],
			$row["invite_code"],
			$row["partner_id"],
			$row["discount"],
			$row["can_reuse_after"],
			$row["expires_at"],
			$row["created_at"],
			$row["updated_at"],
		);
	}

	/**
	 * Получает таблицу
	 */
	protected static function _getTableKey():string {

		return self::_TABLE_NAME;
	}
}