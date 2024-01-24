<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-интерфейс для таблицы pivot_user_10m.mbti_selection_list
 */
class Gateway_Db_PivotUser_MbtiSelectionList extends Gateway_Db_PivotUser_Main {

	protected const _TABLE_KEY = "mbti_selection_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для создания записи
	 *
	 * @throws \queryException
	 * @long - большие структуры
	 */
	public static function insert(Struct_Db_PivotUser_MbtiSelectionList $mbti_selection_list):string {

		$shard_key  = self::_getDbKey($mbti_selection_list->user_id);
		$table_name = self::_TABLE_KEY;

		$insert = [
			"user_id"              => $mbti_selection_list->user_id,
			"mbti_type"            => $mbti_selection_list->mbti_type,
			"created_at"           => time(),
			"updated_at"           => time(),
			"color_selection_list" => $mbti_selection_list->color_selection_list,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert($table_name, $insert);
	}

	/**
	 * метод для создания или обновления записи
	 *
	 */
	public static function insertOrUpdate(Struct_Db_PivotUser_MbtiSelectionList $mbti_selection_list):string {

		$shard_key  = self::_getDbKey($mbti_selection_list->user_id);
		$table_name = self::_TABLE_KEY;

		$insert = [
			"user_id"              => $mbti_selection_list->user_id,
			"mbti_type"            => $mbti_selection_list->mbti_type,
			"text_type"            => $mbti_selection_list->text_type,
			"updated_at"           => time(),
			"color_selection_list" => $mbti_selection_list->color_selection_list,
		];

		return ShardingGateway::database($shard_key)->insertOrUpdate($table_name, $insert);
	}

	/**
	 * метод для обновления записи
	 *
	 * @throws \parseException
	 */
	public static function set(int $user_id, array $set):int {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_TABLE_KEY;

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotUser_MbtiSelectionList::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i LIMIT ?i";

		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $user_id, 1);
	}

	/**
	 * метод для получения записи пользователя
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(int $user_id, string $mbti_type, string $text_type):Struct_Db_PivotUser_MbtiSelectionList {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_TABLE_KEY;

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `mbti_type` = ?s AND `text_type` = ?s LIMIT ?i";

		$row = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, $mbti_type, $text_type, 1);

		if (!isset($row["user_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return new Struct_Db_PivotUser_MbtiSelectionList(
			$row["user_id"],
			$row["mbti_type"],
			$row["text_type"],
			fromJson($row["color_selection_list"])
		);
	}

	/**
	 * метод для получения записи пользователя с выделением
	 *
	 */
	public static function getAllByUserIdAndMbtiType(int $user_id, string $mbti_type):array {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_TABLE_KEY;

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `mbti_type` = ?s AND `text_type` IN (?a) LIMIT ?i";

		$list = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id, $mbti_type, ["short_description", "description"], 2);

		foreach ($list as $k => $v) {
			$list[$k]["color_selection_list"] = fromJson($v["color_selection_list"]);
		}

		return $list;
	}

	/**
	 * метод для получения и блокировки записи пользователя
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getForUpdate(int $user_id):array {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_TABLE_KEY;

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id`=?i LIMIT ?i FOR UPDATE";

		$row = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, 1);

		if (!isset($row["user_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return $row;
	}
}