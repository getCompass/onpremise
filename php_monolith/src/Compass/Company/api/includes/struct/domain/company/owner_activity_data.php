<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Структура для данных активной компании для владельца
 */
class Struct_Domain_Company_OwnerActivityData {

	/**
	 * Struct_Domain_Company_SensitiveActivityData constructor.
	 */
	public function __construct(
		public int $premium_payment_request_count
	) {
	}
}