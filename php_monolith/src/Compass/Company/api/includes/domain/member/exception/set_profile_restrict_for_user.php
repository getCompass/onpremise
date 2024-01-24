<?php

namespace Compass\Company;

use BaseFrame\Exception\DomainException;

/**
 * Недостаточно прав для обновления карточки
 */
class Domain_Member_Exception_SetProfileRestrictForUser extends \DomainException {

	protected array $_output;

	public function __construct(array $_output, string $message = "", int $code = 0, \Throwable $previous = null) {

		$this->_output = $_output;
		parent::__construct($message, $code, $previous);
	}

	/**
	 * Пишем результат вывода
	 */
	public function setOutput(array $_output):void {

		$this->_output = $_output;
	}

	/**
	 * Получаем результат вывода
	 */
	public function getOutput():array {

		if (count($this->_output) < 1) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("exception does not contain _output field");
		}

		return $this->_output;
	}
}
