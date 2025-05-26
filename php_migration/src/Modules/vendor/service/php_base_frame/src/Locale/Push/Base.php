<?php

namespace BaseFrame\Locale\Push;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Базовый класс для формирование текста и локализации пуша
 */
class Base {

	protected const _BASE_ARGS_COUNT = 0; // необходимое количество аргументов
	protected const _BASE_LOCALE_KEY = "RAW_VALUE"; // базовый ключ локализации

	protected string $_locale_key; // ключ локализации
	protected array  $_args = []; // значения для локализации
	protected int    $_args_count; // необходимое количество аргументов для локализации

	/**
	 * Конструктор
	 */
	public function __construct() {

		$this->_locale_key = static::_BASE_LOCALE_KEY;
		$this->_args_count = static::_BASE_ARGS_COUNT;
	}

	/**
	 * Добавить значение в массив
	 *
	 * @param string $arg
	 *
	 * @return Base
	 */
	public function addArg(string $arg):self {

		$this->_args[] = $arg;

		return $this;
	}

	/**
	 * Получить результат локализации
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	public function getLocaleResult():array {

		// если не совпадает количество аргументов и необходимое количество - выкидываем экзепшн
		if (count($this->_args) != $this->_args_count) {
			throw new ParseFatalException("wrong args number");
		}

		return [
			"key"  => $this->_locale_key,
			"args" => $this->_args,
		];
	}
}