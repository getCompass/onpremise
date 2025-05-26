<?php /** @noinspection DuplicatedCode */

declare(strict_types = 1);

/**
 * Файл модуля.
 * Содержит в себе все глобальные функции, необходимые для работы внутри модуля.
 *
 * Общие для модулей глобальные функции должны быть загружены через base frame пакет.
 *
 * @package Compass\Migration
 */

namespace Compass\Migration;

/**
 * Возвращает конфиг, специфичный для модуля.
 * По сути просто обертка, чтобы оставить старый вариант обращения к конфигу внутри модуля.
 *
 * @return array
 */
function getConfig(string $key):array {

	global $config_migration;

	if (is_null($config_migration)) {
		$config_migration = new \Config(MIGRATION_MODULE_API);
	}

	return $config_migration->get($key);
}

/**
 * Перезаписывает конфиг
 *
 * @param string $key
 * @param mixed  $data
 */
function setConfig(string $key, mixed $data):void {

	global $config_migration;

	if (is_null($config_migration)) {
		$config_migration = new \Config(MIGRATION_MODULE_API);
	}

	$config_migration->set($key, $data);
}