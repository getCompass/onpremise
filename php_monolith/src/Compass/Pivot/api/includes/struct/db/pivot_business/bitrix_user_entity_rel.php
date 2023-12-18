<?php

namespace Compass\Pivot;

/**
 * класс описывает структуру записи pivot_business . bitrix_user_entity_rel
 */
class Struct_Db_PivotBusiness_BitrixUserEntityRel {

	public function __construct(
		public int   $user_id,
		public int   $created_at,
		public int   $updated_at,
		public array $bitrix_entity_list,
	) {
	}

	/**
	 * Конвертируем запись в структуру
	 *
	 * @return static
	 */
	public static function convertRow(array $row):self {

		return new Struct_Db_PivotBusiness_BitrixUserEntityRel(
			$row["user_id"],
			$row["created_at"],
			$row["updated_at"],
			fromJson($row["bitrix_entity_list"]),
		);
	}
}