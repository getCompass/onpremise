<?php declare(strict_types = 1);

/**
 * Файл подключения модуля к проекту.
 *
 * Выполняет базовую инициализацию модуля:
 *      — объявляет загрузчик классов
 *      — объявляет константы
 *      — возвращает обработчики путей
 *
 * @package Compass\Thread
 */

namespace Compass\Thread;

define("THREAD_MODULE_ROOT", dirname(__FILE__) . "/");
define("THREAD_MODULE_API", THREAD_MODULE_ROOT . "api/");

// инициализируем конфиг для компании
// его нужно обязательно проинициализировать до того, как начнется обработка запроса
\CompassApp\Conf\Company::init(COMPANY_ID);

// подгружаем данные
include_once THREAD_MODULE_ROOT . "_module/autoload.php";
include_once THREAD_MODULE_ROOT . "_module/function.php";
include_once THREAD_MODULE_ROOT . "_module/sharding.php";

// инициализируем необходимые данные для вендоров
\BaseFrame\Error\ErrorHandler::init(DISPLAY_ERRORS);
\BaseFrame\Server\ServerHandler::init(SERVER_TAG_LIST);
\BaseFrame\Socket\SocketHandler::init(getConfig("SOCKET_URL"), getConfig("SOCKET_MODULE"), SOCKET_KEY_THREAD);
\BaseFrame\Module\ModuleHandler::init(CURRENT_MODULE);
\BaseFrame\Crypt\CryptProvider::init([
	\BaseFrame\Crypt\CryptProvider::DEFAULT => new \BaseFrame\Crypt\CryptData(ENCRYPT_KEY_DEFAULT, ENCRYPT_IV_DEFAULT),
	\BaseFrame\Crypt\CryptProvider::SESSION => new \BaseFrame\Crypt\CryptData(ENCRYPT_KEY_COMPANY_SESSION, ENCRYPT_IV_COMPANY_SESSION),
]);
\BaseFrame\Crypt\PackCryptProvider::init([
	\BaseFrame\Crypt\PackCryptProvider::CONVERSATION => new \BaseFrame\Crypt\PackCryptData(SALT_PACK_CONVERSATION, \BaseFrame\Crypt\CryptProvider::default()),
	\BaseFrame\Crypt\PackCryptProvider::THREAD       => new \BaseFrame\Crypt\PackCryptData(SALT_PACK_THREAD, \BaseFrame\Crypt\CryptProvider::default()),
	\BaseFrame\Crypt\PackCryptProvider::MESSAGE      => new \BaseFrame\Crypt\PackCryptData(SALT_PACK_MESSAGE, \BaseFrame\Crypt\CryptProvider::default()),
	\BaseFrame\Crypt\PackCryptProvider::FILE         => new \BaseFrame\Crypt\PackCryptData(SALT_PACK_FILE, \BaseFrame\Crypt\CryptProvider::default()),
	\BaseFrame\Crypt\PackCryptProvider::CALL         => new \BaseFrame\Crypt\PackCryptData(SALT_PACK_CALL, \BaseFrame\Crypt\CryptProvider::default()),
	\BaseFrame\Crypt\PackCryptProvider::PREVIEW      => new \BaseFrame\Crypt\PackCryptData(SALT_PACK_PREVIEW, \BaseFrame\Crypt\CryptProvider::default()),
	\BaseFrame\Crypt\PackCryptProvider::INVITE       => new \BaseFrame\Crypt\PackCryptData(SALT_PACK_INVITE, \BaseFrame\Crypt\CryptProvider::default()),
	\BaseFrame\Crypt\PackCryptProvider::COMPANY      => new \BaseFrame\Crypt\PackCryptData(SALT_PACK_COMPANY_SESSION, \BaseFrame\Crypt\CryptProvider::session()),
]);
\CompassApp\Company\CompanyHandler::init(COMPANY_ID);
if (isCLi()) {

	\BaseFrame\Conf\ConfHandler::init(
		getConfig("SHARDING_GO"), getConfig("SHARDING_SPHINX"), getConfig("SHARDING_RABBIT"),
		getConfig("SHARDING_MYSQL"), getConfig("SHARDING_MCACHE"), getConfig("GLOBAL_OFFICE_IP")
	);
}

// возвращаем обработчики
return include_once THREAD_MODULE_ROOT . "_module/route.php";