<?php

namespace Compass\Company;

/**
 * Сущность запросов оплаты премиума
 */
class Domain_Premium_Entity_PaymentRequest {

	// сколько живет запрос на оплату
	protected const _REQUEST_LIFETIME = HOUR1;

	/**
	 * Создать запрос на оплату премиума
	 *
	 * @param int $requested_by_user_id
	 *
	 * @return Struct_Db_CompanyData_PremiumPaymentRequest
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \queryException
	 */
	public static function create(int $requested_by_user_id):Struct_Db_CompanyData_PremiumPaymentRequest {

		$requested_at = time();

		try {
			$premium_payment_request = Gateway_Db_CompanyData_PremiumPaymentRequestList::getOne($requested_by_user_id);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

			$premium_payment_request = new Struct_Db_CompanyData_PremiumPaymentRequest(
				$requested_by_user_id,
				0,
				$requested_at,
				$requested_at,
				0,
			);

			Gateway_Db_CompanyData_PremiumPaymentRequestList::insert($premium_payment_request);

			return $premium_payment_request;
		}

		$premium_payment_request->is_payed     = 0;
		$premium_payment_request->requested_at = $requested_at;
		$premium_payment_request->updated_at   = $requested_at;

		Gateway_Db_CompanyData_PremiumPaymentRequestList::set($requested_by_user_id, [
			"is_payed"     => $premium_payment_request->is_payed,
			"requested_at" => $premium_payment_request->requested_at,
			"updated_at"   => $premium_payment_request->updated_at,
		]);

		return $premium_payment_request;
	}

	/**
	 * Получить запись
	 *
	 * @param int $user_id
	 *
	 * @return Struct_Db_CompanyData_PremiumPaymentRequest|false
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function get(int $user_id):Struct_Db_CompanyData_PremiumPaymentRequest|null {

		try {
			return Gateway_Db_CompanyData_PremiumPaymentRequestList::getOne($user_id);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {
			return null;
		}
	}

	/**
	 * Отправить задачу н обновление счетчика руководителям
	 *
	 * @param int $payment_requested_at
	 *
	 * @return int
	 */
	public static function getRequestActiveTill(int $payment_requested_at):int {

		return $payment_requested_at + self::_REQUEST_LIFETIME;
	}
}