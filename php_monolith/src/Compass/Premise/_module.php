<?php declare(strict_types = 1);

/**
 * Файл подключения модуля к проекту.
 *
 * Выполняет базовую инициализацию модуля:
 *      — объявляет загрузчик классов
 *      — объявляет константы
 *      — возвращает обработчики путей
Premise
 * @package Compass\License
 */

namespace Compass\Premise;

define("PREMISE_MODULE_ROOT_PATH", dirname(__FILE__) . "/");
define("PREMISE_MODULE_API_PATH", PREMISE_MODULE_ROOT_PATH . "api/");

// подгружаем данные
include_once PREMISE_MODULE_ROOT_PATH . "_module/autoload.php";
include_once PREMISE_MODULE_ROOT_PATH . "_module/function.php";
include_once PREMISE_MODULE_ROOT_PATH . "_module/sharding.php";

\BaseFrame\Module\ModuleHandler::init(CURRENT_MODULE);

require_once PREMISE_MODULE_ROOT_PATH . "private/custom.php";

// возвращаем обработчики
return include_once PREMISE_MODULE_ROOT_PATH . "_module/route.php";

// @formatter:on