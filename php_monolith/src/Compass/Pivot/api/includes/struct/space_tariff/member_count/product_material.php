<?php

namespace Compass\Pivot;

/**
 * Класс с исходными данными, из которого можно собрать
 * продукт для витрины тарифного плана числа участников.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_SpaceTariff_MemberCount_ProductMaterial {

	public function __construct(
		public int                                  $customer_user_id,
		public Struct_Db_PivotCompany_Company       $space,
		public int                                  $slot_member_count,
		public int                                  $slot_duration,
		public \Tariff\Plan\MemberCount\MemberCount $current_plan,
	) {

		// nothing
	}
}
