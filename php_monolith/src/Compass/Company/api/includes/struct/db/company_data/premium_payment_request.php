<?php

namespace Compass\Company;

/**
 * Класс-структура для таблицы company_data.premium_payment_request_list
 */
class Struct_Db_CompanyData_PremiumPaymentRequest {

	/**
	 * Struct_Db_CompanyData_PremiumPaymentRequestList constructor.
	 *
	 * @param int $requested_by_user_id
	 * @param int $is_payed
	 * @param int $requested_at
	 * @param int $created_at
	 * @param int $updated_at
	 */
	public function __construct(
		public int $requested_by_user_id,
		public int $is_payed,
		public int $requested_at,
		public int $created_at,
		public int $updated_at,
	) {

	}
}
