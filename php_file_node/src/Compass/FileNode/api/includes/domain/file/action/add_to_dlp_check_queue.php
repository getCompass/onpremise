<?php

namespace Compass\FileNode;

/**
 * Действие добавления файла в очередь проверки DLP
 */
class Domain_File_Action_AddToDlpCheckQueue
{
	/**
	 * Выполняем действие
	 *
	 *
	 * @throws Domain_File_Exception_FileNotFound
	 */
	public static function do(array $file_row): void
	{

		if (!Domain_Config_Entity_Icap::instance($file_row["user_id"])->isEnabled()) {
			return;
		}

		// формируем и вставляем запись о проверке в DLP
		$dlp_check_queue_item = new Struct_Db_FileNode_DlpCheckQueue(
			null,
			$file_row["file_type"],
			0,
			time(),
			$file_row["file_key"],
			$file_row["part_path"],
			[]
		);

		Gateway_Db_FileNode_DlpCheckQueue::insert($dlp_check_queue_item);
	}
}
