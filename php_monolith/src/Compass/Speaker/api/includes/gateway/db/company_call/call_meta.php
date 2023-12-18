<?php

namespace Compass\Speaker;

use JetBrains\PhpStorm\Pure;

/**
 * класс-интерфейс для таблицы company_call.call_meta
 */
class Gateway_Db_CompanyCall_CallMeta extends Gateway_Db_CompanyCall_Main {

	protected const _TABLE_NAME = "call_meta";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// создаем запись
	public static function insert(array $insert):int {

		return ShardingGateway::database(self::_getDbKey())->insert(self::_getTableName(), $insert);
	}

	// получаем запись из таблицы
	public static function getOne(string $call_map):array {

		$meta_id = Type_Pack_Call::getMetaId($call_map);

		$query    = "SELECT * FROM `?p` WHERE `meta_id` = ?i LIMIT ?i";
		$meta_row = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_getTableName(), $meta_id, 1);

		if (!isset($meta_row["meta_id"])) {
			throw new \paramException("call not found");
		}

		$meta_row["call_map"] = $call_map;

		unset($meta_row["meta_id"]);

		$meta_row["users"] = fromJson($meta_row["users"]);
		$meta_row["extra"] = fromJson($meta_row["extra"]);

		return $meta_row;
	}

	// получаем все записи
	public static function getAll(array $meta_id_list):array {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query     = "SELECT * FROM `?p` WHERE `meta_id` IN (?a) LIMIT ?i";
		$call_list = ShardingGateway::database(self::_getDbKey())->getAll($query, self::_getTableName(), $meta_id_list, count($meta_id_list));

		$call_list = self::_formatOutputCallList($call_list, $meta_id_list);

		return $call_list;
	}

	// форматируем ответ из базы
	protected static function _formatOutputCallList(array $call_list, array $meta_id_list):array {

		$output = [];
		foreach ($meta_id_list as $k => $v) {

			foreach ($call_list as $item) {

				if ($v != $item["meta_id"]) {
					continue;
				}

				$item["call_map"] = $k;
				unset($item["meta_id"]);
				$item["users"] = fromJson($item["users"]);
				$item["extra"] = fromJson($item["extra"]);

				$output[] = $item;
			}
		}

		return $output;
	}

	// получаем запись из таблицы на обновление
	public static function getOneForUpdate(string $call_map):array {

		$meta_id = Type_Pack_Call::getMetaId($call_map);

		$query    = "SELECT * FROM `?p` WHERE `meta_id` = ?i LIMIT ?i FOR UPDATE";
		$meta_row = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_getTableName(), $meta_id, 1);

		$meta_row["call_map"] = $call_map;

		unset($meta_row["meta_id"]);

		$meta_row["users"] = fromJson($meta_row["users"]);
		$meta_row["extra"] = fromJson($meta_row["extra"]);

		return $meta_row;
	}

	// обновляем запись
	public static function set(string $call_map, array $set):void {

		$meta_id = Type_Pack_Call::getMetaId($call_map);

		ShardingGateway::database(self::_getDbKey())->update("UPDATE `?p` SET ?u WHERE `meta_id` = ?i LIMIT ?i",
			self::_getTableName(), $set, $meta_id, 1);
	}

	// обновляем запись
	public static function setByMetaId(int $meta_id, array $set):void {

		ShardingGateway::database(self::_getDbKey())->update("UPDATE `?p` SET ?u WHERE `meta_id` = ?i LIMIT ?i",
			self::_getTableName(), $set, $meta_id, 1);
	}

	// -------------------------------------------------------
	// методы для работы с полем extra
	// -------------------------------------------------------

	// текущая версия
	protected const _EXTRA_VERSION = 3;

	// схема extra
	protected const _EXTRA_SCHEMA = [
		"call_id"           => 0,
		"hangup_by_user_id" => 0,
	];

	// создать новую структуру для extra
	public static function initExtraSchema(int $call_id):array {

		// получаем текущую схему extra
		$extra_schema = self::_EXTRA_SCHEMA;

		// устанавливаем персональные параметры
		$extra_schema["call_id"] = $call_id;

		// устанавливаем текущую версию
		$extra_schema["version"] = self::_EXTRA_VERSION;

		return $extra_schema;
	}

	// устанавливаем пользователя, который повесил трубку
	#[Pure]
	public static function setHangUpUserId(array $extra_schema, int $user_id):array {

		$extra_schema                      = self::_getEXTRASCHEMA($extra_schema);
		$extra_schema["hangup_by_user_id"] = $user_id;

		return $extra_schema;
	}

	// получаем call_id
	#[Pure]
	public static function getReportCallId(array $extra_schema):int {

		$extra_schema = self::_getEXTRASCHEMA($extra_schema);

		return $extra_schema["call_id"];
	}

	// получить актуальную структуру для extra
	#[Pure]
	protected static function _getExtraSchema(array $extra_schema):array {

		// если версия не совпадает - дополняем её до текущей
		if ($extra_schema["version"] != self::_EXTRA_VERSION) {

			$extra_schema            = array_merge(self::_EXTRA_SCHEMA, $extra_schema);
			$extra_schema["version"] = self::_EXTRA_VERSION;
		}

		return $extra_schema;
	}

	/**
	 * функция возвращает название таблицы
	 *
	 */
	protected static function _getTableName():string {

		return self::_TABLE_NAME;
	}
}