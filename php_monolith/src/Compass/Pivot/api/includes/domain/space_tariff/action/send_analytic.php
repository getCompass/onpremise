<?php

namespace Compass\Pivot;

/**
 * Класс для работы с отправкой аналитики по тарифу
 */
class Domain_SpaceTariff_Action_SendAnalytic {

	protected const _EVENT_KEY = "tariff";

	public const PAYMENT_TYPE_FIRST_PAYMENT = "first_payment"; // первая оплата
	public const PAYMENT_TYPE_TARIFF_CHANGE = "tariff_change"; // изменение тарифа
	public const PAYMENT_TYPE_PROLONGATION  = "prolongation";  // продление срока
	public const PAYMENT_TYPE_FROM_TRIAL    = "from_trial";    // из триала в платый тариф
	public const PAYMENT_TYPE_FROM_FREE     = "from_free";     // из бесплатного в платый тариф

	/**
	 * Пишем аналитику по тарифу
	 */
	public static function do(int    $payed_by_user_id, int $space_id, int $payed_until, int $tariff_price, int $received_money, bool $is_trial,
					  int    $discount_price_value, int $payed_month_count, int $payed_tariff, string $payed_currency,
					  string $promocode, string $payment_type, string $payment_device, string $payment_source):void {

		Gateway_Bus_CollectorAgent::init()->log(self::_EVENT_KEY, [
			"space_id"             => $space_id,
			"payed_by_user_id"     => $payed_by_user_id,
			"created_at"           => time(),
			"payed_until"          => $payed_until,
			"tariff_price"         => $tariff_price,
			"received_money"       => $received_money,
			"is_trial"             => $is_trial,
			"discount_price_value" => $discount_price_value,
			"payed_month_count"    => $payed_month_count,
			"payed_tariff"         => $payed_tariff,
			"payed_currency"       => $payed_currency,
			"promocode"            => $promocode,
			"payment_type"         => $payment_type,
			"payment_source"       => $payment_source,
			"payment_device"       => $payment_device,
		]);
	}
}