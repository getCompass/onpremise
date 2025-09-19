<?php declare(strict_types=1);

/**
 * Файл подключения request-обработчика модуля к проекту.
 * Отвечает за работу с внешними запросами.
 *
 * Выполняет базовую инициализацию модуля:
 *	— объявляет загрузчик классов
 * 	— объявляет константы
 * 	— возвращает обработчики путей
 *
 * @package Compass\Announcement
 */

namespace Compass\Announcement;

define("ANNOUNCEMENT_MODULE_ROOT", dirname(__FILE__) . "/");
define("ANNOUNCEMENT_MODULE_API", ANNOUNCEMENT_MODULE_ROOT . "api/");

require_once ANNOUNCEMENT_MODULE_ROOT . "private/custom.php";
require_once ANNOUNCEMENT_MODULE_ROOT . "private/main.php";

require_once ANNOUNCEMENT_MODULE_API . "includes/custom_define.php";

// инициализируем данные в вендоре
\BaseFrame\Error\ErrorHandler::init(DISPLAY_ERRORS);
\BaseFrame\Server\ServerHandler::init(SERVER_TAG_LIST, SERVICE_LABEL);
\BaseFrame\Socket\SocketHandler::init(getConfig("SOCKET_URL"), getConfig("SOCKET_MODULE"), SOCKET_KEY_ANNOUNCEMENT, CA_CERTIFICATE);
\BaseFrame\Conf\ConfHandler::init(
	getConfig("SHARDING_GO"), getConfig("SHARDING_SPHINX"), getConfig("SHARDING_RABBIT"),
	getConfig("SHARDING_MYSQL"), getConfig("SHARDING_MCACHE"), getConfig("GLOBAL_OFFICE_IP")
);
\BaseFrame\Module\ModuleHandler::init(CURRENT_MODULE);

// подгружаем данные
include_once ANNOUNCEMENT_MODULE_ROOT . "_module/autoload.php";
include_once ANNOUNCEMENT_MODULE_ROOT . "_module/function.php";
include_once ANNOUNCEMENT_MODULE_ROOT . "_module/sharding.php";

// возвращаем обработчики
return include_once ANNOUNCEMENT_MODULE_ROOT . "_module/route.php";