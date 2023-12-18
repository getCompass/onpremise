<?php

namespace Compass\Pivot;

/**
 * класс-структура для response от API-шлюза смс-провайдера
 */
class Struct_Gateway_Sms_Provider_Response {

	public int              $http_status_code;
	public int              $request_send_at_ms;
	public int|string|array $body;

	/**
	 * Struct_Gateway_Sms_Provider_Response constructor.
	 *
	 * @mixed
	 */
	public function __construct(int $http_status_code, int $request_send_at_ms, int|string|array $body) {

		$this->http_status_code   = $http_status_code;
		$this->request_send_at_ms = $request_send_at_ms;
		$this->body               = $body;
	}
}