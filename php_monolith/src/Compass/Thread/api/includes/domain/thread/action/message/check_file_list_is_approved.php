<?php

namespace Compass\Thread;

use BaseFrame\Exception\Request\InappropriateContentException;

class Domain_Thread_Action_Message_CheckFileListIsApproved
{
	// статус загрузки файла (дублирование с php_file_balancer)
	public const FILE_STATUS_PROCESSING = 0;
	public const FILE_STATUS_APPROVED   = 1;
	public const FILE_STATUS_RESTRICTED = 2;
	public const FILE_STATUS_DELETED    = 3;

	/**
	 * Проверяем, разрешены ли файлы из списка сообщений
	 */
	public static function do(array $raw_message_list): void
	{

		$file_map_list = [];
		foreach ($raw_message_list as $message) {

			if (isset($message["file_map"]) && $message["file_map"] !== false) {
				$file_map_list[] = $message["file_map"];
			}
		}

		$grouped_file_list = self::_groupFileMapListByTable($file_map_list);
		$file_list         = self::_getFileList($grouped_file_list);

		foreach ($file_list as $file) {

			if ($file["status"] !== self::FILE_STATUS_APPROVED) {
				throw new InappropriateContentException("file is not approved");
			}
		}
	}

	/**
	 * Группируем массив файлов по таблицам.
	 */
	protected static function _groupFileMapListByTable(array $file_map_list): array
	{

		$grouped_file_list = [];

		foreach ($file_map_list as $file_map) {

			$shard_id = \CompassApp\Pack\File::getShardId($file_map);
			$table_id = \CompassApp\Pack\File::getTableId($file_map);

			$full_table_name = "{$shard_id}.{$table_id}";
			$meta_id         = \CompassApp\Pack\File::getMetaId($file_map);

			$grouped_file_list[$full_table_name][$file_map] = $meta_id;
		}

		return $grouped_file_list;
	}

	/**
	 * Получаем записи из базы.
	 */
	protected static function _getFileList(array $grouped_file_list): array
	{

		$output = [];

		foreach ($grouped_file_list as $k => $v) {

			[$shard_id, $table_id] = explode(".", $k);
			$file_list             = Gateway_Db_CompanyData_File::getAll($shard_id, (int) $table_id, $v);

			array_push($output, ...$file_list);
		}

		return $output;
	}
}
