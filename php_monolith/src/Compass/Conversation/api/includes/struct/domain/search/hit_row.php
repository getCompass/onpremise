<?php declare(strict_types=1);

namespace Compass\Conversation;

/**
 * Запись из поискового движка для совпадения.
 * Здесь нет явных сущностей, нужно восстановить их из связи search-entity.
 *
 * @property Struct_Domain_Search_HitRowNestedLocation[] $extra_list
 * @property Struct_Domain_Search_HitRowNestedLocation[] $nested_location_list
 */
#[\JetBrains\PhpStorm\Immutable(\JetBrains\PhpStorm\Immutable::PROTECTED_WRITE_SCOPE)]
class Struct_Domain_Search_HitRow {

	public array $extra_list = [];
	public array $nested_location_list = [];

	/**
	 * Данные поискового движка для найденной локации.
	 */
	public function __construct(
		public int   $search_id,
		public int   $type,
		public int   $attribute_mask,
		public int   $creator_id,
		public int   $updated_at,
		public int   $direct_parent_search_id,
		public array $parent_search_id_list,
		public int   $field_hit_mask,
	) {

		// nothing
	}

	/**
	 * Статический конструктор для данных из поисковой таблицы.
	 */
	public static function fromRow(array $row):static {

		$parent_id_list = $row["inherit_parent_id_list"] !== ""
			? array_map(static fn(string $el) => (int)$el, explode(",", $row["inherit_parent_id_list"]))
			: [];

		$parent_id_list[] = (int)$row["parent_id"];

		$matches = [];
		preg_match("#field_mask=(\d)#", $row["rankfactors"], $matches);
		$field_hit_mask = isset($matches[1]) ? (int)$matches[1] : 0;

		return new static(
			(int)$row["search_id"],
			(int)$row["type"],
			(int)$row["attribute_mask"],
			(int)$row["creator_id"],
			(int)($row["updated_at"] ?? 0),
			(int)$row["parent_id"],
			$parent_id_list,
			$field_hit_mask,
		);
	}

	/**
	 * Добавляет экстра данные к совпадению.
	 * Как правило это совпадение другой сущности, но связанной с этой.
	 */
	public function attachExtra(Struct_Domain_Search_HitRow $hit_row):void {

		$this->updated_at   = max($this->updated_at, $hit_row->updated_at);
		$this->extra_list[] = $hit_row;
	}

	/**
	 * Добавляет экстра данные к совпадению.
	 * Как правило это совпадение другой сущности, но связанной с этой.
	 */
	public function attachLocation(Struct_Domain_Search_HitRow $location_hit_row, array $hit_row_list, int $total_hit_count):void {

		foreach ($hit_row_list as $hit_row) {
			$this->updated_at = max($this->updated_at, $hit_row->updated_at);
		}

		$this->nested_location_list[] = new Struct_Domain_Search_HitRowNestedLocation($location_hit_row, $hit_row_list, $total_hit_count);
	}
}
