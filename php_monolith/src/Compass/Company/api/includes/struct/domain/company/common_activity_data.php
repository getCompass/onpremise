<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Структура для данных активной компании для всех пользователей
 */
class Struct_Domain_Company_CommonActivityData {

	/**
	 * Struct_Domain_Company_CommonActivityData constructor.
	 */
	public function __construct(
		public int    $premium_payment_request_active_till,
		public string $general_group_conversation_key,
	) {
	}
}