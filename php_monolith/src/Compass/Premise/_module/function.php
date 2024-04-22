<?php /** @noinspection DuplicatedCode */

declare(strict_types = 1);

/**
 * Файл модуля.
 * Содержит в себе все глобальные функции, необходимые для работы внутри модуля.
 *
 * Общие для модулей глобальные функции должны быть загружены через base frame пакет.
 *
 * @package Compass\License
 */

namespace Compass\Premise;

/**
 * Возвращает конфиг, специфичный для модуля.
 * По сути просто обертка, чтобы оставить старый вариант обращения к конфигу внутри модуля.
 */
function getConfig(string $key):array {

	global $config_intercom;

	if (is_null($config_intercom)) {
		$config_intercom = new \Config(PREMISE_MODULE_API_PATH);
	}

	return $config_intercom->get($key);
}

/**
 * Перезаписывает конфиг.
 */
function setConfig(string $key, mixed $data):void {

	global $config_intercom;

	if (is_null($config_intercom)) {
		$config_intercom = new \Config(PREMISE_MODULE_API_PATH);
	}

	$config_intercom->set($key, $data);
}