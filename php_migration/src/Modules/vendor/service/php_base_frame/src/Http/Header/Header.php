<?php

namespace BaseFrame\Http\Header;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Обработчик хедеров HTTP
 */
class Header {

	// ключ хедера
	protected const _HEADER_KEY = "";

	// значение хедера
	protected string $_value;

	/**
	 * Конструктор
	 *
	 * @throws ParseFatalException
	 */
	public function __construct() {

		if ($this::class == Header::class) {
			throw new ParseFatalException("cant create raw header instance");
		}

		if (!isset($_SERVER["HTTP_" . static::_HEADER_KEY])) {

			$this->_value = "";
			return;
		}
		$this->_value = $_SERVER["HTTP_" . static::_HEADER_KEY];
	}

	/**
	 * Получить значение хедера
	 *
	 * @return string
	 */
	public function getValue():string {

		return $this->_value;
	}
}