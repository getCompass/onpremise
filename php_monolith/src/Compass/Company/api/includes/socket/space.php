<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Сокеты для работы с пространством
 */
class Socket_Space extends \BaseFrame\Controller\Socket {

	/** @var string[] поддерживаемые методы */
	public const ALLOW_METHODS = [
		"getInfoForPurchase",
		"getEventCountInfo",
	];

	/**
	 * Получить информацию для покупки товара
	 */
	public function getInfoForPurchase():array {

		[$is_administrator, $space_created_at] = Domain_SpaceTariff_Scenario_Socket::getInfoForPurchase($this->user_id);

		return $this->ok([
			"is_administrator" => (int) $is_administrator,
			"space_created_at" => (int) $space_created_at,
		]);
	}

	/**
	 * Получить информацию по действиями в компании
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	public function getEventCountInfo():array {

		[$total_event_count, $previous_week_event_count, $current_week_event_count] = Domain_Company_Scenario_Socket::getEventCountInfo();

		return $this->ok([
			"total_event_count"         => (int) $total_event_count,
			"previous_week_event_count" => (int) $previous_week_event_count,
			"current_week_event_count"  => (int) $current_week_event_count,
		]);
	}
}