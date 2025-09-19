<?php

declare(strict_types=1);

namespace Compass\FileNode;

/**
 * Структура для задач dlp проверки
 */
class Struct_Db_FileNode_DlpCheckQueue
{
	public function __construct(
		public ?int $queue_id,
		public int $file_type,
		public int $error_count,
		public int $need_work,
		public string $file_key,
		public string $part_path,
		public array $extra,
	) {
	}
}
