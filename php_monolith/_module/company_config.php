<?php declare(strict_types=1);

/**
 * Файл модуля.
 * Содержит в себе все глобальные функции, необходимые для работы внутри модуля.
 *
 * Общие для модулей глобальные функции должны быть загружены через base frame пакет.
 *
 */

use CompassApp\Conf\Company;

/**
 * Возвращает конфиг, специфичный для модуля.
 * По сути просто обертка, чтобы оставить старый вариант обращения к конфигу внутри модуля.
 *
 * @return array
 */
function getCompanyConfig(string $key):mixed {

	global $company_config;

	if (is_null($company_config)) {
		$company_config = \CompassApp\Conf\Company::init(COMPANY_ID);
	}

	return $company_config->get($key);
}
