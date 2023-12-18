<?php

namespace Compass\Company;

/**
 * Структура данных премиума для компании.
 */
class Struct_Premium_CompanyData {

	/**
	 * Constructor
	 */
	public function __construct(
		public int  $user_id,
		public bool $need_block_if_premium_inactive,
		public int  $premium_active_till,
	) {

	}
}
