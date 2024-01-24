<?php declare(strict_types=1);

namespace Compass\Conversation;

/**
 * Данные задачи индексации.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Domain_Search_Task {

	/**
	 * Родитель совпадения.
	 * Может быть как прямым, так и наследованным.
	 */
	public function __construct(
		public int $task_id,
		public int $type,
		public int $error_count,
		public int $created_at,
		public int $updated_at,
		public array|object $data,
	) {

		// nothing
	}

	/**
	 * Формирует задачу при создании сущности.
	 */
	public static function fromDeclaration(int $type, array|object $data):static {

		return new static(0, $type, 0, time(), time(), $data);
	}

	/**
	 * Формирует задачу из записи БД.
	 */
	public static function fromRow(array $row):static {

		return new static(
			(int) $row["task_id"],
			(int) $row["type"],
			(int) $row["error_count"],
			(int) $row["created_at"],
			(int) $row["updated_at"],
			fromJson($row["data"]),
		);
	}
}
