<?php /** @noinspection DuplicatedCode */

declare(strict_types=1);

/**
 * Файл модуля.
 * Содержит в себе все глобальные функции, необходимые для работы внутри модуля.
 *
 * Общие для модулей глобальные функции должны быть загружены через base frame пакет.
 *
 * @package Compass\Thread
 */

namespace Compass\Thread;

/**
 * Возвращает конфиг, специфичный для модуля.
 * По сути просто обертка, чтобы оставить старый вариант обращения к конфигу внутри модуля.
 *
 * @return array
 */
function getConfig(string $key):array {

	global $config_thread;

	if (is_null($config_thread)) {
        $config_thread = new \Config(THREAD_MODULE_API);
	}

	return $config_thread->get($key);
}

/**
 * Перезаписывает конфиг
 *
 * @param string $key
 * @param mixed $data
 */
function setConfig(string $key, mixed $data):void {

    global $config_thread;

    if (is_null($config_thread)) {
        $config_thread = new \Config(THREAD_MODULE_API);
    }

    $config_thread->set($key, $data);
}

/**
 * Возвращает конфиг текущей компании.
 *
 * @return array
 */
function getCompanyConfig(string $key):mixed {

	return \CompassApp\Conf\Company::instance()->get($key);
}

/**
 * Задает значение для конфига текущей компании.
 *
 * @return array
 */
function setCompanyConfig(string $key, mixed $value):void {

	\CompassApp\Conf\Company::instance()->set($key, $value);
}