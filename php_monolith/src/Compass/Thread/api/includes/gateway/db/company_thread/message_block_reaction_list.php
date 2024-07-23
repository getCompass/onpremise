<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс интерфейс для работы с таблицей message_block_reaction_list
 * в ней храним реакции для одного БЛОКА
 */
class Gateway_Db_CompanyThread_MessageBlockReactionList extends Gateway_Db_CompanyThread_Main {

	protected const _TABLE_KEY = "message_block_reaction_list";

	// -------------------------------------------------------
	// PUBLIC
	// -------------------------------------------------------

	// получаем одну запись
	public static function getOne(string $thread_map, int $block_id):Struct_Db_CompanyThread_MessageBlockReaction {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$row = ShardingGateway::database($db_key)->getOne("SELECT * FROM `?p` WHERE thread_map=?s AND `block_id`=?i LIMIT ?i",
			$table_key, $thread_map, $block_id, 1);

		if (!isset($row["thread_map"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_formatRow($row);
	}

	/**
	 * Получаем последние несколько блоков
	 *
	 * @param string $thread_map
	 * @param int    $count
	 *
	 * @return Struct_Db_CompanyThread_MessageBlockReaction[]
	 * @throws \parseException
	 */
	public static function getLastBlocks(string $thread_map, int $count = 1):array {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$list = ShardingGateway::database($db_key)->getAll("SELECT * FROM `?p` WHERE thread_map=?s ORDER BY `block_id` DESC LIMIT ?i",
			$table_key, $thread_map, $count);

		foreach ($list as $k => $v) {
			$list[$k] = self::_formatRow($v);
		}

		return $list;
	}

	/**
	 * получаем несколько записей через IN
	 *
	 * @param string $thread_map
	 * @param array  $block_id_list
	 *
	 * @return Struct_Db_CompanyThread_MessageBlockReaction[]
	 * @throws \parseException
	 */
	public static function getList(string $thread_map, array $block_id_list):array {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$list = ShardingGateway::database($db_key)->getAll("SELECT * FROM `?p` WHERE thread_map=?s AND `block_id` IN (?a) LIMIT ?i",
			$table_key, $thread_map, $block_id_list, count($block_id_list));

		foreach ($list as $v) {
			$output[$v["block_id"]] = self::_formatRow($v);
		}

		return $output ?? [];
	}

	/**
	 * Пишем в бд напрямую реакции
	 *
	 * @throws \queryException
	 * @throws ParseFatalException
	 */
	public static function insert(array $insert):void {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		ShardingGateway::database($db_key)->insert($table_key, $insert);
	}

	/**
	 * Пишем пачку записей в бд
	 *
	 * @throws ParseFatalException
	 */
	public static function insertList(array $insert_list):int {

		$table_key = self::_getTableKey();

		return ShardingGateway::database(self::_getDbKey())->insertArray($table_key, $insert_list);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получаем таблицу
	protected static function _getTableKey():string {

		return self::_TABLE_KEY;
	}

	/**
	 * преобразовываем массив в структуру
	 */
	protected static function _formatRow(array $row):Struct_Db_CompanyThread_MessageBlockReaction {

		return new Struct_Db_CompanyThread_MessageBlockReaction(
			$row["thread_map"],
			$row["block_id"],
			$row["created_at"],
			$row["updated_at"],
			fromJson($row["reaction_data"]),
		);
	}
}