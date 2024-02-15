<?php declare(strict_types = 1);

/**
 * Файл подключения модуля к проекту.
 *
 * Выполняет базовую инициализацию модуля:
 *      — объявляет загрузчик классов
 *      — объявляет константы
 *      — возвращает обработчики путей
 *
 * @package Compass\FileBalancer
 */

namespace Compass\FileBalancer;

const PIVOT_SERVER = "pivot";
const CLOUD_SERVER = "domino";

if (!defined("CURRENT_SERVER")) {
	define("CURRENT_SERVER", COMPANY_ID > 0 ? CLOUD_SERVER : PIVOT_SERVER);
}

define("FILEBALANCER_MODULE_ROOT", dirname(__FILE__) . "/");
define("FILEBALANCER_MODULE_API", FILEBALANCER_MODULE_ROOT . "api/");

require_once FILEBALANCER_MODULE_ROOT . "private/custom.php";
require_once FILEBALANCER_MODULE_ROOT . "private/main.php";

require_once FILEBALANCER_MODULE_API . "includes/user.php";
require_once FILEBALANCER_MODULE_API . "includes/custom_define.php";
require_once FILEBALANCER_MODULE_API . "includes/custom_exception.php";

// подгружаем данные
include_once FILEBALANCER_MODULE_ROOT . "_module/autoload.php";
include_once FILEBALANCER_MODULE_ROOT . "_module/function.php";
include_once FILEBALANCER_MODULE_ROOT . "_module/sharding.php";

// инициализируем необходимые данные для вендоров
\BaseFrame\Error\ErrorHandler::init(DISPLAY_ERRORS);
\BaseFrame\Server\ServerHandler::init(SERVER_TAG_LIST);
\BaseFrame\Socket\SocketHandler::init(getConfig("SOCKET_URL"), getConfig("SOCKET_MODULE"), SOCKET_KEY_FILE_BALANCER);
\BaseFrame\Module\ModuleHandler::init(CURRENT_MODULE);
\BaseFrame\Crypt\CryptProvider::init([
	\BaseFrame\Crypt\CryptProvider::DEFAULT => new \BaseFrame\Crypt\CryptData(ENCRYPT_KEY_DEFAULT, ENCRYPT_IV_DEFAULT),
	\BaseFrame\Crypt\CryptProvider::SESSION => new \BaseFrame\Crypt\CryptData(ENCRYPT_KEY_COMPANY_SESSION, ENCRYPT_IV_COMPANY_SESSION),
]);
\BaseFrame\Crypt\PackCryptProvider::init([
	\BaseFrame\Crypt\PackCryptProvider::FILE    => new \BaseFrame\Crypt\PackCryptData(SALT_PACK_FILE, \BaseFrame\Crypt\CryptProvider::default()),
	\BaseFrame\Crypt\PackCryptProvider::COMPANY => new \BaseFrame\Crypt\PackCryptData(SALT_PACK_COMPANY_SESSION, \BaseFrame\Crypt\CryptProvider::session()),
]);

if (CURRENT_SERVER == CLOUD_SERVER) {

	\CompassApp\Company\HibernationHandler::init(NEED_COMPANY_HIBERNATE, COMPANY_HIBERNATION_DELAYED_TIME);

	\CompassApp\Company\CompanyHandler::init(COMPANY_ID);
	if (isCLi()) {

		\BaseFrame\Conf\ConfHandler::init(
			getConfig("SHARDING_GO"), getConfig("SHARDING_SPHINX"), getConfig("SHARDING_RABBIT"),
			getConfig("SHARDING_MYSQL"), getConfig("SHARDING_MCACHE"), getConfig("GLOBAL_OFFICE_IP")
		);
	}
} else {

	\BaseFrame\Conf\ConfHandler::init(
		getConfig("SHARDING_GO"), getConfig("SHARDING_SPHINX"), getConfig("SHARDING_RABBIT"),
		getConfig("SHARDING_MYSQL"), getConfig("SHARDING_MCACHE"), getConfig("GLOBAL_OFFICE_IP")
	);
}

// возвращаем обработчики
return include_once FILEBALANCER_MODULE_ROOT . "_module/route.php";

// @formatter:on