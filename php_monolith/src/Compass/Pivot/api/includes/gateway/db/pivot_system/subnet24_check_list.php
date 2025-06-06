<?php

namespace Compass\Pivot;

/**
 * модель для таблицы
 * сгенерирована автоматически
 * Type_CodeGen_DbGateway::do("pivot_system", "subnet_24_check_list");
 */
class Gateway_Db_PivotSystem_Subnet24CheckList extends Gateway_Db_PivotSystem_Main {

	/** @var string имя таблицы */
	protected const _TABLE_KEY = "subnet_24_check_list";

	// -------------------------------------------------------
	// AUTOGENERATED FUNCTIONS
	// -------------------------------------------------------

	// вставляем запись
	public static function insert(
		int   $subnet_24,
		int   $status,
		int   $checked_ip,
		int   $need_work,
		array $extra,
	):int {

		$insert = [
			"subnet_24"  => $subnet_24,
			"status"     => $status,
			"checked_ip" => $checked_ip,
			"need_work"  => $need_work,
			"created_at" => time(),
			"extra"      => $extra,
		];
		return ShardingGateway::database(self::_getDbKey())->insert(self::_TABLE_KEY, $insert);
	}

	// получаем запись
	public static function get(int $subnet_24):Struct_Db_PivotSystem_Subnet24CheckList {

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE `subnet_24`=?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $subnet_24, 1);
		return self::_formatRow($row);
	}

	// проверяем существует ли запись
	public static function isExist(int $subnet_24):bool {

		try {
			self::get($subnet_24);
			return true;
		} catch (\cs_RowIsEmpty) {
			return false;
		}
	}

	// обновляем запись
	public static function update(int $subnet_24, array $set):int {

		$set["updated_at"] = time();

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "UPDATE `?p` SET ?u WHERE `subnet_24`=?i LIMIT ?i";
		return ShardingGateway::database(self::_getDbKey())->update($query, self::_TABLE_KEY, $set, $subnet_24, 1);
	}

	// получаем записи для работы
	public static function getNextWorkList(int $limit, int $offset):array {

		// запрос проверен на EXPLAIN (INDEX=`status.need_work`)
		$query = "SELECT * FROM `?p` WHERE `status` = ?i AND `need_work` <= ?i LIMIT ?i OFFSET ?i";
		$list  = ShardingGateway::database(self::_getDbKey())->getAll($query, self::_TABLE_KEY,
			Domain_Subnet_Entity_Check::STATUS_NEED_CHECK, time(), $limit, $offset);

		return self::_formatList($list);
	}

	// получаем записи по массиву из первичных ключей
	public static function getListByIn(array $in):array {

		if (sizeof($in) == 0) {
			return [];
		}

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE `subnet_24` IN (?a) LIMIT ?i";
		$list  = ShardingGateway::database(self::_getDbKey())->getAll($query, self::_TABLE_KEY, $in, sizeof($in));
		return self::_formatList($list);
	}

	/**
	 * Обновляем список записей
	 */
	public static function updateList(array $subnet_24_list, array $set):int {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		return ShardingGateway::database(self::_DB_KEY)
			->update("UPDATE `?p` SET ?u WHERE `subnet_24` IN (?a) LIMIT ?i", self::_TABLE_KEY, $set, $subnet_24_list, count($subnet_24_list));
	}

	// -------------------------------------------------------
	// CUSTOM FUNCTIONS
	// -------------------------------------------------------

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * форматируем список записей из базы
	 *
	 * @return Struct_Db_PivotSystem_Subnet24CheckList[]
	 */
	protected static function _formatList(array $list):array {

		return array_map([self::class, "_formatRow"], $list);
	}

	// форматируем одну запись из базы
	protected static function _formatRow(array $row):Struct_Db_PivotSystem_Subnet24CheckList {

		if (empty($row)) {
			throw new \cs_RowIsEmpty();
		}
		return Struct_Db_PivotSystem_Subnet24CheckList::rowToStruct($row);
	}
}