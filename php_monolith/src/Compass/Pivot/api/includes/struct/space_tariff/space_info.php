<?php

namespace Compass\Pivot;

/**
 * Вспомогательный класс для генерации витрин.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_SpaceTariff_SpaceInfo {

	/**
	 * Конструктор.
	 */
	public function __construct(
		public Struct_Db_PivotCompany_Company $space,
		public int                            $space_occupied_at,
	) {

		// nothing
	}
}
