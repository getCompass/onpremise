<?php

namespace Compass\Pivot;

/**
 * Вспомогательный класс для передачи данных
 * после парсинга goods_id обратно в вызывающую функцию.
 */
#[\JetBrains\PhpStorm\Immutable]
abstract class Struct_SpaceTariff_ActivationItem {

	/**
	 * Конструктор.
	 */
	public function __construct(
		public int                         $customer_user_id,
		public int                         $space_id,
		public string                      $plan_type,
		public \Tariff\Plan\BaseAlteration $alteration
	) {

		// nothing
	}
}