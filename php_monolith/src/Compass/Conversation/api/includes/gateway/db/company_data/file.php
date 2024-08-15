<?php

namespace Compass\Conversation;

/**
 * Класс-шлюз для работы с таблицами company_data.file_list.
 *
 * Этот класс нарушает логику распределения, но нужен для оптимизации
 * индексации файлов для поиска. Все согласовано.
 */
class Gateway_Db_CompanyData_File extends Gateway_Db_CompanyData_Main {

	/** @var string имя таблицы */
	protected const _TABLE_NAME = "file_list";

	/**
	 * Возвращает все записи для запрошенных файлов.
	 */
	public static function getAll(string $shard_id, int $table_id, array $meta_id_list):array {

		$db_key    = static::_getDbKey();
		$table_key = static::_getTableName();

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query     = "SELECT * FROM `?p` WHERE `meta_id` IN (?a) AND `year` = ?i AND `month` = ?i LIMIT ?i";
		$file_list = ShardingGateway::database($db_key)->getAll($query, $table_key, $meta_id_list, $shard_id, $table_id, count($meta_id_list));

		return self::_formatOutputFileList($file_list, $meta_id_list);
	}

	/**
	 * Возвращает название таблицы
	 */
	protected static function _getTableName():string {

		return static::_TABLE_NAME;
	}

	/**
	 * Форматирует ответ из базы
	 */
	protected static function _formatOutputFileList(array $file_list, array $meta_id_list):array {

		$output = [];

		foreach ($meta_id_list as $k => $v) {

			foreach ($file_list as $item) {

				if ($v != $item["meta_id"]) {
					continue;
				}

				$output[] = self::_formatRow($item, $k);
			}
		}

		return $output;
	}

	/**
	 * Убирает системные данные из записи при форматировании ответа.
	 */
	protected static function _formatRow(array $file_row, string $file_map):array {

		$file_row["file_map"] = $file_map;
		$file_row["extra"]    = fromJson($file_row["extra"]);

		unset($file_row["meta_id"]);

		if (isset($file_row["year"])) {
			unset($file_row["year"]);
		}

		if (isset($file_row["month"])) {
			unset($file_row["month"]);
		}

		if (is_null($file_row["content"])) {
			$file_row["content"] = "";
		}

		return $file_row;
	}
}