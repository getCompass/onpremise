<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_mail_service.send_queue
 */
class Struct_Db_PivotMailService_Task {

	/**
	 * Struct_Db_PivotMailService_Task constructor.
	 */
	public function __construct(
		public string $message_id,
		public int    $stage,
		public int    $need_work,
		public int    $error_count,
		public int    $created_at_ms,
		public int    $updated_at,
		public int    $task_expire_at,
		public string $mail,
		public string $title,
		public string $content,
		public array  $extra,
	) {
	}

	/**
	 * конвертируем запись в структуру
	 *
	 * @return Struct_Db_PivotMailService_Task
	 */
	public static function rowToStruct(array $row):Struct_Db_PivotMailService_Task {

		return new Struct_Db_PivotMailService_Task(
			(string) $row["message_id"],
			(int) $row["stage"],
			(int) $row["need_work"],
			(int) $row["error_count"],
			(int) $row["created_at_ms"],
			(int) $row["updated_at"],
			(int) $row["task_expire_at"],
			(string) $row["mail"],
			(string) $row["title"],
			(string) $row["content"],
			is_array($row["extra"]) ? $row["extra"] : fromJson($row["extra"]),
		);
	}
}