<?php

namespace BaseFrame\Exception\Request;

use BaseFrame\Exception\RequestException;

/**
 * Ошибка бизнес-логики приложения
 */
class CaseException extends RequestException {

	const HTTP_CODE = 200;

	protected int   $_error_code;
	protected array $_extra;

	public function __construct(int $error_code, string $message, array $extra = []) {

		$this->_error_code = $error_code;
		$this->message     = $message;
		$this->_extra      = $extra;
		parent::__construct($message);
	}

	/**
	 * Вернуть код ошибки
	 *
	 * @return int
	 */
	public function getErrorCode():int {

		return $this->_error_code;
	}

	/**
	 * Вернуть экстру
	 *
	 * @return array
	 */
	public function getExtra():array {

		return $this->_extra;
	}
}