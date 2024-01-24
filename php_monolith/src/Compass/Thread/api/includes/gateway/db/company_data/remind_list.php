<?php

namespace Compass\Thread;

/**
 * класс-интерфейс для таблицы company_data . remind_list
 */
class Gateway_Db_CompanyData_RemindList extends Gateway_Db_CompanyData_Main {

	protected const _TABLE_KEY = "remind_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для вставки записи
	 */
	public static function insert(int $remind_type, int $remind_at, int $creator_user_id, string $recipient_id, array $data):Struct_Db_CompanyData_Remind {

		$insert    = [
			"type"            => $remind_type,
			"is_done"         => 0,
			"remind_at"       => $remind_at,
			"creator_user_id" => $creator_user_id,
			"created_at"      => time(),
			"updated_at"      => 0,
			"recipient_id"    => $recipient_id,
			"data"            => $data,
		];
		$remind_id = static::_connect()->insert(self::_TABLE_KEY, $insert);

		$insert["remind_id"] = formatInt($remind_id);
		$insert["data"]      = toJson($data);

		return self::_formatRow($insert);
	}

	/**
	 * получаем запись
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getOne(int $remind_id):Struct_Db_CompanyData_Remind {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE `remind_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $remind_id, 1);

		if (!isset($row["remind_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_formatRow($row);
	}

	/**
	 * метод для обновления записи
	 */
	public static function set(int $remind_id, array $set):void {

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "UPDATE `?p` SET ?u WHERE `remind_id` = ?i LIMIT ?i";
		static::_connect()->update($query, self::_TABLE_KEY, $set, $remind_id, 1);
	}

	/**
	 * удаляем запись
	 */
	public static function delete(int $remind_id):void {

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "DELETE FROM `?p` WHERE `remind_id` = ?i LIMIT ?i";
		static::_connect()->delete($query, self::_TABLE_KEY, $remind_id, 1);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * преобразовываем массив в структуру
	 *
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