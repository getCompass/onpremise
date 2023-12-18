<?php declare(strict_types=1);

namespace Compass\Conversation;

/**
 * Запись из поискового движка.
 * Здесь нет явных сущностей, нужно восстановить их из связи search_id-entity.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Domain_Search_LocationRow {

	/**
	 * Данные поискового движка для найденной локации.
	 */
	public function __construct(
		public int $search_id,
		public int $type,
		public int $hit_count,
		public int $last_hit_at,
	) {

		// nothing
	}

	/**
	 * Статический конструктор для данных из поисковой таблицы.
	 */
	public static function fromRow(array $row):static {

		return new static(
			(int) $row["parent_id"],
			(int) $row["type"],
			(int) $row["hit_count"],
			(int) $row["last_updated_at"],
		);
	}
}
