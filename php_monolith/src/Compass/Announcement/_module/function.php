<?php /** @noinspection DuplicatedCode */

declare(strict_types = 1);

/**
 * Файл модуля.
 * Содержит в себе все глобальные функции, необходимые для работы внутри модуля.
 *
 * Общие для модулей глобальные функции должны быть загружены через base frame пакет.
 *
 * @package Compass\Announcement
 */

namespace Compass\Announcement;

/**
 * Возвращает конфиг, специфичный для модуля.
 * По сути просто обертка, чтобы оставить старый вариант обращения к конфигу внутри модуля.
 */
function getConfig(string $key):array {

	global $config_announcement;

	if (is_null($config_announcement)) {
		$config_announcement = new \Config(ANNOUNCEMENT_MODULE_API);
	}

	return $config_announcement->get($key);
}

/**
 * Перезаписывает конфиг.
 */
function setConfig(string $key, mixed $data):void {

	global $config_announcement;

	if (is_null($config_announcement)) {
		$config_announcement = new \Config(ANNOUNCEMENT_MODULE_API);
	}

	$config_announcement->set($key, $data);
}