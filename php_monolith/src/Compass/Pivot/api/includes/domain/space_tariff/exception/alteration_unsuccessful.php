<?php

namespace Compass\Pivot;

/**
 * Альтерация завершилась неудачей.
 */
class Domain_SpaceTariff_Exception_AlterationUnsuccessful extends \BaseFrame\Exception\DomainException {

	// маппинг известных кодов ошибок для клиентского апи
	protected const _KNOWN_CLIENT_API_ERROR_LIST = [
		\Tariff\Plan\MemberCount\BasePlan::ERROR_PLAN_WAS_CHANGED => 1214004,
		\Tariff\Plan\MemberCount\OptionLimit::ERROR_EXCEEDED      => 1214005,
	];

	// маппинг известных кодов ошибок для внутреннего апи
	protected const _KNOWN_CLIENT_SOCKET_ERROR_LIST = [
		\Tariff\Plan\MemberCount\BasePlan::ERROR_PLAN_WAS_CHANGED => 1412004,
	];

	/**
	 * Конструктор.
	 */
	public function __construct(string $message, int $code) {

		$this->code = $code;
		parent::__construct($message);
	}

	/**
	 * Проверяет, является ли ошибка известной для api.
	 */
	public function getKnowApiError():int {

		return $this::_KNOWN_CLIENT_API_ERROR_LIST[$this->code] ?? 0;
	}

	/**
	 * Проверяет, является ли ошибка известной для api.
	 */
	public function getKnowSocketError():int {

		return $this::_KNOWN_CLIENT_API_ERROR_LIST[$this->code] ?? 0;
	}
}