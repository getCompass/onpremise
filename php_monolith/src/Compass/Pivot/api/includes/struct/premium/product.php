<?php

namespace Compass\Pivot;

/**
 * Структура продукта для продления премиума.
 */
class Struct_Premium_Product {

	/**
	 * Constructor
	 */
	public function __construct(
		public string $label,
		public bool   $is_default,
		public array  $platform_list,
		public bool   $is_promo,
		public array  $data,
		public array  $bundle,
		public array  $extra_requirement,
	) {

	}
}
