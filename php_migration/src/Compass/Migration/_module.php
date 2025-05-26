<?php declare(strict_types=1);

/**
 * Файл подключения модуля к проекту.
 *
 * Выполняет базовую инициализацию модуля:
 *      — объявляет загрузчик классов
 *      — объявляет константы
 *      — возвращает обработчики путей
 *
 * @package Compass\Migration
 */

namespace Compass\Migration;

define("MIGRATION_MODULE_ROOT", dirname(__FILE__) . "/");
define("MIGRATION_MODULE_API", MIGRATION_MODULE_ROOT . "api/");

// подгружаем данные
include_once MIGRATION_MODULE_ROOT . "_module/autoload.php";
include_once MIGRATION_MODULE_ROOT . "_module/function.php";
include_once MIGRATION_MODULE_ROOT . "_module/sharding.php";

// инициализируем необходимые данные для вендоров
\BaseFrame\Error\ErrorHandler::init(DISPLAY_ERRORS);
\BaseFrame\Server\ServerHandler::init(SERVER_TAG_LIST);
\BaseFrame\Socket\SocketHandler::init(getConfig("SOCKET_URL"), getConfig("SOCKET_MODULE"), SOCKET_KEY_MIGRATION, CA_CERTIFICATE);
\BaseFrame\Module\ModuleHandler::init(CURRENT_MODULE);
if (isCLi()) {

	\BaseFrame\Conf\ConfHandler::init(
		getConfig("SHARDING_GO"), getConfig("SHARDING_SPHINX"), getConfig("SHARDING_RABBIT"),
		getConfig("SHARDING_MYSQL"), getConfig("SHARDING_MCACHE"), getConfig("GLOBAL_OFFICE_IP")
	);
}

// возвращаем обработчики
return include_once MIGRATION_MODULE_ROOT . "_module/route.php";

// @formatter:on