<?php declare(strict_types = 1);

/**
 * Файл подключения модуля к проекту.
 *
 * Выполняет базовую инициализацию модуля:
 *      — объявляет загрузчик классов
 *      — объявляет константы
 *      — возвращает обработчики путей
 *
 * @package Compass\FileNode
 */

namespace Compass\FileNode;

define("FILENODE_MODULE_ROOT", dirname(__FILE__) . "/");
define("FILENODE_MODULE_API", FILENODE_MODULE_ROOT . "api/");

require_once FILENODE_MODULE_ROOT . "private/custom.php";
require_once FILENODE_MODULE_ROOT . "private/main.php";

require_once FILENODE_MODULE_API . "includes/user.php";
require_once FILENODE_MODULE_API . "includes/custom_define.php";
require_once FILENODE_MODULE_API . "includes/custom_exception.php";

// подгружаем данные
include_once FILENODE_MODULE_ROOT . "_module/autoload.php";
include_once FILENODE_MODULE_ROOT . "_module/function.php";
include_once FILENODE_MODULE_ROOT . "_module/sharding.php";

# инициализируем вендор
\BaseFrame\Url\UrlHandler::init(PIVOT_DOMAIN);
\BaseFrame\Error\ErrorHandler::init(DISPLAY_ERRORS);
\BaseFrame\Server\ServerHandler::init(SERVER_TAG_LIST, SERVICE_LABEL);
\BaseFrame\Module\ModuleHandler::init(CURRENT_MODULE);
\BaseFrame\Socket\SocketHandler::init(getConfig("SOCKET_URL"), getConfig("SOCKET_MODULE"), SOCKET_KEY_ME);

\BaseFrame\Conf\ConfHandler::init(
	getConfig("SHARDING_GO"), getConfig("SHARDING_SPHINX"), getConfig("SHARDING_RABBIT"),
	getConfig("SHARDING_MYSQL"), getConfig("SHARDING_MCACHE"), getConfig("GLOBAL_OFFICE_IP")
);

\BaseFrame\Crypt\CryptProvider::init([
	\BaseFrame\Crypt\CryptProvider::DEFAULT => new \BaseFrame\Crypt\CryptData(ENCRYPT_KEY_DEFAULT, ENCRYPT_IV_DEFAULT),
]);
\BaseFrame\Crypt\PackCryptProvider::init([
	\BaseFrame\Crypt\PackCryptProvider::FILE => new \BaseFrame\Crypt\PackCryptData(SALT_PACK_FILE, \BaseFrame\Crypt\CryptProvider::default()),
]);

// возвращаем обработчики
return include_once FILENODE_MODULE_ROOT . "_module/route.php";

// @formatter:on