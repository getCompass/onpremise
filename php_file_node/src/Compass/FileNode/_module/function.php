<?php /** @noinspection DuplicatedCode */

declare(strict_types = 1);

/**
 * Файл модуля.
 * Содержит в себе все глобальные функции, необходимые для работы внутри модуля.
 *
 * Общие для модулей глобальные функции должны быть загружены через base frame пакет.
 *
 * @package Compass\FileNode
 */

namespace Compass\FileNode;

/**
 * Возвращает конфиг, специфичный для модуля.
 * По сути просто обертка, чтобы оставить старый вариант обращения к конфигу внутри модуля.
 *
 * @return array
 */
function getConfig(string $key):array {

	global $config_file_balancer;

	if (is_null($config_file_balancer)) {
		$config_file_balancer = new \Config(FILENODE_MODULE_API);
	}

	return $config_file_balancer->get($key);
}

/**
 * Перезаписывает конфиг
 *
 * @param string $key
 * @param mixed  $data
 */
function setConfig(string $key, mixed $data):void {

	global $config_file_balancer;

	if (is_null($config_file_balancer)) {
		$config_file_balancer = new \Config(FILENODE_MODULE_API);
	}

	$config_file_balancer->set($key, $data);
}