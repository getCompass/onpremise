<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс для формирования текста и локализации для описания интеграций
 */
class Domain_System_Entity_Locale_Integration_Description extends Domain_System_Entity_Locale_Integration
{
	protected const _BASE_LOCALE_KEY = "INTEGRATION_DESCRIPTION"; // базовый ключ локализации

	protected string $_integration_name = "unknown";

	/**
	 * Конструктор
	 *
	 * @throws ParseFatalException
	 */
	public function __construct(string $integration_name)
	{

		parent::__construct();

		$this->_integration_name = $integration_name;
	}
}
