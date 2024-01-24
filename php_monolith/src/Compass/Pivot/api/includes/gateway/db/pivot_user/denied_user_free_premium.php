<?php

namespace Compass\Pivot;

/**
 * Класс-интерфейс для таблицы pivot_user_{n}m.denied_user_free_premium
 * Таблица хранит данные о пользователей, который недоступен бесплатный премиум по той или иной причине.
 */
class Gateway_Db_PivotUser_DeniedUserFreePremium extends Gateway_Db_PivotUser_Main {

	/** @var string имя таблицы */
	protected const _TABLE_KEY = "denied_user_free_premium";

	/**
	 * Добавить запись в таблицу.
	 * <b>После добавления пользователь не сможет активировать бесплатный премиум.</b>
	 */
	public static function insert(int $user_id, int $reason_type):Struct_Db_PivotUser_DeniedUserFreePremium {

		$insert = [
			"user_id"     => $user_id,
			"created_at"  => time(),
			"reason_type" => $reason_type,
		];

		ShardingGateway::database(self::_getDbKey($user_id))->insert(self::_TABLE_KEY, $insert);
		return static::_fromRow($insert);
	}

	/**
	 * Выбирает одну запись для пользователя.
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getOne(int $user_id):Struct_Db_PivotUser_DeniedUserFreePremium {

		// EXPLAIN: INDEX PRIMARY
		$query  = "SELECT * FROM `?p` WHERE `user_id` = ?i LIMIT ?i";
		$result = ShardingGateway::database(self::_getDbKey($user_id))->getOne($query, self::_TABLE_KEY, $user_id, 1);

		if (!isset($result["user_id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("user $user_id has no record");
		}

		return static::_fromRow($result);
	}

	/**
	 * Удаляем запись из базы.
	 */
	public static function delete(int $user_id):void {

		// EXPLAIN: INDEX PRIMARY
		$query  = "DELETE FROM `?p` WHERE `user_id` = ?i LIMIT ?i";
		ShardingGateway::database(self::_getDbKey($user_id))->delete($query, self::_TABLE_KEY, $user_id, 1);
	}

	# region protected

	/**
	 * Конвертирует запись в структуру.
	 */
	protected static function _fromRow(array $row):Struct_Db_PivotUser_DeniedUserFreePremium {

		return new Struct_Db_PivotUser_DeniedUserFreePremium(...$row);
	}

	# endregion protected
}