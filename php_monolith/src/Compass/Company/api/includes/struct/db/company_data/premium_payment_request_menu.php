<?php

namespace Compass\Company;

/**
 * Класс-структура для таблицы company_data.premium_payment_request_menu
 */
class Struct_Db_CompanyData_PremiumPaymentRequestMenu {

	/**
	 * Struct_Db_CompanyData_PremiumPaymentRequestList constructor.
	 *
	 * @param int $user_id
	 * @param int $requested_by_user_id
	 * @param int $is_unread
	 * @param int $created_at
	 * @param int $updated_at
	 */
	public function __construct(
		public int $user_id,
		public int $requested_by_user_id,
		public int $is_unread,
		public int $created_at,
		public int $updated_at,
	) {

	}
}
