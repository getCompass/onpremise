<?php

namespace Compass\Pivot;

/**
 * класс описывающий структуру таблицы
 */
class Struct_Db_PivotAttribution_UserCampaignRel {

	public function __construct(
		public int    $user_id,
		public string $visit_id,
		public string $utm_tag,
		public string $source_id,
		public string $link,
		public int    $is_direct_reg,
		public int    $created_at,
	) {
	}

	/**
	 * Конвертируем запись БД в структуру
	 *
	 * @return static
	 */
	public static function rowToStruct(array $row):self {

		return new self(
			$row["user_id"],
			$row["visit_id"],
			$row["utm_tag"],
			$row["source_id"],
			$row["link"],
			$row["is_direct_reg"],
			$row["created_at"],
		);
	}
}