<?php /** @noinspection DuplicatedCode */

declare(strict_types = 1);

/**
 * Файл модуля.
 * Содержит в себе все глобальные функции, необходимые для работы внутри модуля.
 *
 * Общие для модулей глобальные функции должны быть загружены через base frame пакет.
 *
 * @package Compass\Pivot
 */

namespace Compass\Pivot;

/**
 * Возвращает конфиг, специфичный для модуля.
 * По сути просто обертка, чтобы оставить старый вариант обращения к конфигу внутри модуля.
 */
function getConfig(string $key):array {

	global $config_pivot;

	if (is_null($config_pivot)) {
		$config_pivot = new \Config(PIVOT_MODULE_API);
	}

	return $config_pivot->get($key);
}

/**
 * Перезаписывает конфиг.
 */
function setConfig(string $key, mixed $data):void {

	global $config_pivot;

	if (is_null($config_pivot)) {
		$config_pivot = new \Config(PIVOT_MODULE_API);
	}

	$config_pivot->set($key, $data);
}