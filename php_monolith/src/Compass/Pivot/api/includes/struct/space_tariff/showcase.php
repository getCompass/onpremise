<?php

namespace Compass\Pivot;

/**
 * Класс с витриной, нужен для типизации.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_SpaceTariff_Showcase {

	/**
	 * Конструктор.
	 */
	public function __construct(
		public string $type,
		public object $action_list,
		public object $promo_list,
	) {

		// nothing
	}
}
