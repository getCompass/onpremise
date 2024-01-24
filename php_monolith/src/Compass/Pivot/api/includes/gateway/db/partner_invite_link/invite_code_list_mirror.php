<?php

namespace Compass\Pivot;

/**
 * Класс-интерфейс для таблицы partner_data . invite_code_list_mirror
 */
class Gateway_Db_PartnerInviteLink_InviteCodeListMirror extends Gateway_Db_PartnerInviteLink_Main {

	protected const _TABLE_NAME = "invite_code_list_mirror";

	/**
	 * Создаем запись
	 */
	public static function insert(Struct_Db_PartnerInviteLink_InviteCodeMirror $invite_code_mirror):void {

		$insert_array = [
			"invite_code" => $invite_code_mirror->invite_code,
			"partner_id"  => $invite_code_mirror->partner_id,
			"created_at"  => $invite_code_mirror->created_at,
		];
		ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_NAME, $insert_array);
	}

	/**
	 * Метод для получения записи компании
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(string $invite_code):Struct_Db_PartnerInviteLink_InviteCodeMirror {

		$db_key     = self::_getDbKey();
		$table_name = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `invite_code` = ?s LIMIT ?i";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_name, $invite_code, 1);
		if (!isset($row["invite_code"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToObject($row);
	}

	/**
	 * Удаляем запись
	 */
	public static function delete(string $invite_code):void {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "DELETE FROM `?p` WHERE `invite_code` = ?s LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->update($query, self::_TABLE_NAME, $invite_code, 1);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Создаем структуру из строки бд
	 */
	protected static function _rowToObject(array $row):Struct_Db_PartnerInviteLink_InviteCodeMirror {

		return new Struct_Db_PartnerInviteLink_InviteCodeMirror(
			$row["invite_code"],
			$row["partner_id"],
			$row["created_at"],
		);
	}

	/**
	 * Получает таблицу
	 */
	protected static function _getTableKey():string {

		return self::_TABLE_NAME;
	}
}