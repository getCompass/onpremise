<?php

namespace Compass\Pivot;

/**
 * Класс-интерфейс для работы с таблицей автономных систем.
 *
 * В рантайме в таблицу данные не заносятся —
 * она статична, поэтому методов вставки и обновления нет
 */
class Gateway_Db_PivotSystem_AutonomousSystem extends Gateway_Db_PivotSystem_Main {

	/** @var string имя таблицы */
	protected const _TABLE_KEY = "autonomous_system";

	/**
	 * Возвращает запись по нормализованному ip-адресу.
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getOne(int $normalized_ip):Struct_Db_PivotSystem_AutonomousSystem {

		// EXPLAIN: INDEX get_in_range
		$query  = "SELECT * FROM `?p` WHERE `ip_range_start` <= ?i AND `ip_range_end` >= ?i LIMIT ?i";
		$result = ShardingGateway::database(static::_DB_KEY)->getOne($query, static::_TABLE_KEY, $normalized_ip, $normalized_ip, 1);

		if (!isset($result["id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("autonomous system not found");
		}

		return static::_toStruct($result);
	}

	# region protected

	/**
	 * Конвертирует запись базы в структуру.
	 */
	protected static function _toStruct(array $raw):Struct_Db_PivotSystem_AutonomousSystem {

		unset($raw["id"]);
		return new Struct_Db_PivotSystem_AutonomousSystem(...$raw);
	}

	# endregion protected
}
