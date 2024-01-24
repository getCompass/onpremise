<?php

namespace Compass\Pivot;

/**
 * Элемент витрины тарифного плана количества участников
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_SpaceTariff_MemberCount_ShowcaseItem {

	/**
	 * Конструктор.
	 */
	public function __construct(
		public string                              $goods_id,
		public \Tariff\Plan\MemberCount\Alteration $alteration,
		public int                                 $prolong_duration,
		public int                                 $limit,
		public int                                 $available_till = 0
	) {

		// nothing
	}
}