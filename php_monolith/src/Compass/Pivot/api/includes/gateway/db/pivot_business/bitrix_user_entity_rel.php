<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-интерфейс для таблицы pivot_business.bitrix_user_entity_rel
 */
class Gateway_Db_PivotBusiness_BitrixUserEntityRel extends Gateway_Db_PivotBusiness_Main {

	protected const _TABLE_KEY = "bitrix_user_entity_rel";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод вставки записи в базу
	 *
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PivotBusiness_BitrixUserEntityRel $bitrix_user_entity_rel):string {

		$insert = [
			"user_id"            => $bitrix_user_entity_rel->user_id,
			"created_at"         => $bitrix_user_entity_rel->created_at,
			"updated_at"         => $bitrix_user_entity_rel->updated_at,
			"bitrix_entity_list" => $bitrix_user_entity_rel->bitrix_entity_list,
		];

		// осуществляем запрос
		return ShardingGateway::database(self::_getDbKey())->insert(self::_getTableKey(), $insert);
	}

	/**
	 * метод для обновления записи
	 *
	 * @throws \parseException
	 */
	public static function set(int $user_id, array $set):int {

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotBusiness_BitrixUserEntityRel::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i LIMIT ?i";
		return ShardingGateway::database(self::_getDbKey())->update($query, self::_getTableKey(), $set, $user_id, 1);
	}

	/**
	 * метод для получения записи
	 *
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getOne(int $user_id):Struct_Db_PivotBusiness_BitrixUserEntityRel {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_getTableKey(), $user_id, 1);

		if (!isset($row["user_id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("row not found");
		}

		return Struct_Db_PivotBusiness_BitrixUserEntityRel::convertRow($row);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получает таблицу
	protected static function _getTableKey():string {

		return self::_TABLE_KEY;
	}
}