<?php

namespace BaseFrame\Exception\Gateway;

use BaseFrame\Exception\GatewayException;

/**
 * Ошибка бизнес-логики в сокет запросе
 */
class SocketException extends GatewayException {

	// экстра информация об ошибке
	protected array $_extra;

	public function __construct(string $message, array $response = []) {

		// записываем экстру
		unset($response["error_code"]);
		$this->_extra = $response;

		parent::__construct($message);
	}

	/**
	 * Вернуть extra
	 *
	 * @return array
	 */
	public function getExtra():array {

		return $this->_extra;
	}
}