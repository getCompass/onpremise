<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс для формирования текста и локализации для интеграций
 */
class Domain_System_Entity_Locale_Integration extends Domain_System_Entity_Locale_Base
{
	protected const _BASE_LOCALE_KEY = "INTEGRATION"; // базовый ключ локализации

	protected string $_integration_name;

	/**
	 * Конструктор
	 *
	 * @throws ParseFatalException
	 */
	public function __construct()
	{

		parent::__construct();
	}

	/**
	 * Получить результат локализации
	 *
	 * @throws ParseFatalException
	 */
	public function getLocaleResult(): array
	{

		$this->_locale_key = strtoupper($this->_integration_name) . "_" . $this->_locale_key;

		return parent::getLocaleResult();
	}
}
