<?php

namespace Compass\Pivot;

/**
 * Вспомогательный класс для передачи данных
 * после парсинга goods_id обратно в вызывающую функцию.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_SpaceTariff_MemberCount_ActivationItem extends Struct_SpaceTariff_ActivationItem {

	/**
	 * Конструктор.
	 */
	public function __construct(
		int                                 $customer_user_id,
		int                                 $space_id,
		public string                       $plan_type,
		\Tariff\Plan\MemberCount\Alteration $alteration
	) {

		parent::__construct($customer_user_id, $space_id, $plan_type, $alteration);
	}
}