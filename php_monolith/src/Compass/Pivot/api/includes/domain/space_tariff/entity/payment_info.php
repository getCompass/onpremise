<?php

namespace Compass\Pivot;

/**
 * Класс для формирования платежной информации оплаты плана пространства.
 */
class Domain_SpaceTariff_Entity_PaymentInfo {

	protected const _CURRENT_SCHEMA_VERSION = 1;

	/**
	 * Создает массив платежной информации.
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["version" => "int", "data" => "array"])]
	public static function make(int $customer_user_id, string $payment_id = "", int $method = 0, int $prolongation_duration = 0):array {

		$method_name = match ($method) {

			\Tariff\Plan\BaseAction::METHOD_PAYMENT  => "payment",
			\Tariff\Plan\BaseAction::METHOD_PROMO    => "promo",
			\Tariff\Plan\BaseAction::METHOD_DETACHED => "detached",
			\Tariff\Plan\BaseAction::METHOD_FORCE    => "force",
			default                                  => "other"
		};

		return [
			"version" => static::_CURRENT_SCHEMA_VERSION,
			"data"    => [
				"customer_user_id"      => $customer_user_id,
				"payment_id"            => $payment_id,
				"method"                => $method_name,
				"prolongation_duration" => $prolongation_duration,
			],
		];
	}

	/**
	 * Получаем ID плательщика
	 *
	 * @return int
	 */
	public static function getCustomerUserID(array $payment_info):int {

		return $payment_info["data"]["customer_user_id"];
	}

	/**
	 * Получаем ID платежа
	 *
	 * @return string
	 */
	public static function getPaymentID(array $payment_info):string {

		return $payment_info["data"]["payment_id"];
	}
}
