<?php

namespace BaseFrame\Exception\Request;

/**
 * Требуется оплата пространства
 */
class PaymentRequiredException extends \BaseFrame\Exception\RequestException {

	public const RESTRICTED_ERROR_CODE = 2038001;
	public const LIMIT_ERROR_CODE      = 2038002;

	const HTTP_CODE = 402;

	protected int   $_error_code;
	protected array $_extra;

	public function __construct(int $error_code, string $message, array $extra = []) {

		$this->_error_code = $error_code;
		$this->message     = $message;
		$this->_extra      = $extra;
		parent::__construct($error_code);
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