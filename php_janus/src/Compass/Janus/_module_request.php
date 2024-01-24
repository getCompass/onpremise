<?php declare(strict_types=1);

/**
 * Файл подключения request-обработчика модуля к проекту.
 * Отвечает за работу с внешними запросами.
 *
 * Выполняет базовую инициализацию модуля:
 *    — объявляет загрузчик классов
 *    — объявляет константы
 *    — возвращает обработчики путей
 *
 * @package Compass\Janus
 */

namespace Compass\Janus;

define("JANUS_MODULE_ROOT", dirname(__FILE__) . "/");
define("JANUS_MODULE_API", JANUS_MODULE_ROOT . "api/");

// подгружаем данные
include_once JANUS_MODULE_ROOT . "_module/autoload.php";
include_once JANUS_MODULE_ROOT . "_module/function.php";
include_once JANUS_MODULE_ROOT . "_module/sharding.php";

// возвращаем обработчики
return include_once JANUS_MODULE_ROOT . "_module/route.php";