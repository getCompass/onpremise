<?php

namespace Compass\Company;

/**
 * Сокеты для работы с данными премиум-статуса.
 */
class Socket_Premium extends \BaseFrame\Controller\Socket {

	/** @var string[] поддерживаемые методы */
	public const ALLOW_METHODS = [
		"updatePremiumStatuses",
		"onInvoiceCreated",
		"onInvoicePayed",
		"onInvoiceCanceled",
	];

	/**
	 * Обновляет данные премиума для пользователя.
	 */
	public function updatePremiumStatuses():array {

		$premium_company_data_list = $this->post(\Formatter::TYPE_ARRAY, "premium_company_data_list");

		Domain_User_Scenario_Socket::updatePremiumStatuses($premium_company_data_list);
		return $this->ok();
	}

	/**
	 * Был создан счет на оплату
	 */
	public function onInvoiceCreated():array {

		$created_by_user_id = $this->post(\Formatter::TYPE_INT, "created_by_user_id");

		Domain_User_Scenario_Socket::onInvoiceCreated($created_by_user_id);

		return $this->ok();
	}

	/**
	 * Был оплачен счет
	 */
	public function onInvoicePayed():array {

		Domain_User_Scenario_Socket::onInvoicePayed();

		return $this->ok();
	}

	/**
	 * Счет был отменен
	 */
	public function onInvoiceCanceled():array {

		$invoice_id = $this->post(\Formatter::TYPE_INT, "invoice_id");

		Domain_User_Scenario_Socket::onInvoiceCanceled($invoice_id);

		return $this->ok();
	}
}