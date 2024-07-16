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
 * @package Compass\Jitsi
 */

namespace Compass\Jitsi;

define("JITSI_MODULE_ROOT", dirname(__FILE__) . "/");
define("JITSI_MODULE_API", JITSI_MODULE_ROOT . "api/");

// подгружаем данные
include_once JITSI_MODULE_ROOT . "_module/autoload.php";
include_once JITSI_MODULE_ROOT . "_module/function.php";
include_once JITSI_MODULE_ROOT . "_module/sharding.php";

// инициализируем данные в вендоре
\BaseFrame\Error\ErrorHandler::init(DISPLAY_ERRORS);
\BaseFrame\Server\ServerHandler::init(SERVER_TAG_LIST);

\BaseFrame\Crypt\CryptProvider::init([
	\BaseFrame\Crypt\CryptProvider::DEFAULT  => new \BaseFrame\Crypt\CryptData(ENCRYPT_KEY_DEFAULT, ENCRYPT_IV_DEFAULT),
	\BaseFrame\Crypt\CryptProvider::EXTENDED => new \BaseFrame\Crypt\CryptData(EXTENDED_ENCRYPT_KEY_DEFAULT, EXTENDED_ENCRYPT_IV_DEFAULT),
]);

\BaseFrame\Crypt\PackCryptProvider::init([
	\BaseFrame\Crypt\PackCryptProvider::FILE => new \BaseFrame\Crypt\PackCryptData(SALT_PACK_FILE, \BaseFrame\Crypt\CryptProvider::default())
]);

\BaseFrame\Socket\SocketHandler::init(getConfig("SOCKET_URL"), getConfig("SOCKET_MODULE"), SOCKET_KEY_JITSI);

\BaseFrame\Module\ModuleHandler::init(CURRENT_MODULE);

// возвращаем обработчики
return include_once JITSI_MODULE_ROOT . "_module/route.php";