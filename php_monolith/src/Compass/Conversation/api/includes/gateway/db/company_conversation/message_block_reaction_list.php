<?php

namespace Compass\Conversation;

/**
 * класс интерфейс для работы с таблицей message_block_reaction_list
 * в ней храним реакции для одного БЛОКА
 */
class Gateway_Db_CompanyConversation_MessageBlockReactionList extends Gateway_Db_CompanyConversation_Main {

	protected const _TABLE_KEY = "message_block_reaction_list";

	// -------------------------------------------------------
	// PUBLIC
	// -------------------------------------------------------

	// получаем одну запись
	public static function getOne(string $conversation_map, int $block_id):Struct_Db_CompanyConversation_MessageBlockReaction {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$row = static::_connect($db_key)->getOne("SELECT * FROM `?p` WHERE conversation_map=?s AND `block_id`=?i LIMIT ?i",
			$table_key, $conversation_map, $block_id, 1);

		if (!isset($row["conversation_map"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_formatRow($row);
	}

	/**
	 * Получаем последние несколько блоков
	 *
	 * @param string $conversation_map
	 * @param int    $count
	 *
	 * @return Struct_Db_CompanyConversation_MessageBlockReaction[]
	 */
	public static function getLastBlocks(string $conversation_map, int $count = 1):array {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$list = static::_connect($db_key)->getAll("SELECT * FROM `?p` WHERE conversation_map=?s ORDER BY `block_id` DESC LIMIT ?i",
			$table_key, $conversation_map, $count);

		foreach ($list as $k => $v) {
			$list[$k] = self::_formatRow($v);
		}

		return $list;
	}

	/**
	 * получаем несколько записей через IN
	 *
	 * @param string $conversation_map
	 * @param array  $block_id_list
	 *
	 * @return Struct_Db_CompanyConversation_MessageBlockReaction[]
	 */
	public static function getList(string $conversation_map, array $block_id_list):array {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$list = static::_connect($db_key)->getAll("SELECT * FROM `?p` WHERE conversation_map=?s AND `block_id` IN (?a) LIMIT ?i",
			$table_key, $conversation_map, $block_id_list, count($block_id_list));

		foreach ($list as $k => $v) {
			$list[$k] = self::_formatRow($v);
		}

		return $list;
	}

	/**
	 * Возвращает конкретные блоки с реакциями для конкретных диалогов.
	 */
	public static function getSpecifiedList(array $block_list_by_conversation_map):array {

		$table_data = [];
		$limit      = 0;

		foreach ($block_list_by_conversation_map as $conversation_map => $block_id_list) {

			$limit += count($block_id_list);
			array_push($table_data, $conversation_map, $block_id_list);
		}

		$output       = [];
		$sharding_key = self::_getDbKey();
		$table_name   = self::_getTableKey();

		// EXPLAIN USE INDEX PRIMARY
		$where_expression = implode(" OR ", array_fill(0, count($table_data) / 2, "(`conversation_map` = ?s AND `block_id` IN (?a))"));
		$query            = "SELECT * FROM `?p` WHERE $where_expression LIMIT ?i";

		$block_list = static::_connect($sharding_key)->getAll(...[$query, $table_name, ...$table_data, $limit]);
		$output[]   = array_map(static fn(array $row):Struct_Db_CompanyConversation_MessageBlockReaction => static::_formatRow($row), $block_list);

		return array_merge(...$output);
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
	protected static function _formatRow(array $row):Struct_Db_CompanyConversation_MessageBlockReaction {

		return new Struct_Db_CompanyConversation_MessageBlockReaction(
			$row["conversation_map"],
			$row["block_id"],
			$row["created_at"],
			$row["updated_at"],
			fromJson($row["reaction_data"]),
		);
	}
}