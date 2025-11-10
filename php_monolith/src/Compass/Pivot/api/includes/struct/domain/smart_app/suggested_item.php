<?php

declare(strict_types = 1);

namespace Compass\Pivot;

/**
 * Структура для элемента каталога приложений
 */
class Struct_Domain_SmartApp_SuggestedItem {

	/**
	 * Struct_Domain_SmartApp_SuggestedItem constructor.
	 */
	public function __construct(
		public int    $catalog_item_id,
		public int    $is_popular,
		public int    $sort_weight,
		public string $catalog_category,
		public string $uniq_name,
		public string $title,
		public string $url,
		public int    $is_need_custom_user_agent,
		public int    $is_need_show_in_catalog,
	) {
	}

	// формируем объект из массива
	public static function rowToStruct(array $row):self {

		return new self(
			$row["catalog_item_id"],
			$row["is_popular"],
			$row["sort_weight"],
			$row["catalog_category"],
			$row["uniq_name"],
			$row["title"],
			$row["url"],
			$row["is_need_custom_user_agent"],
			$row["is_need_show_in_catalog"]
		);
	}
}