<?php

declare(strict_types = 1);

namespace Compass\Userbot;

/**
 * Файл подключения модуля к проекту.
 *
 * Выполняет базовую инициализацию модуля:
 *      — объявляет загрузчик классов
 *      — объявляет константы
 *      — возвращает обработчики путей
 *
 * @package Compass\Userbot
 */

define("USERBOT_MODULE_ROOT", dirname(__FILE__) . "/");
define("USERBOT_MODULE_API", USERBOT_MODULE_ROOT . "api/");

\BaseFrame\Server\ServerHandler::init(SERVER_TAG_LIST);

// подгружаем данные
include_once USERBOT_MODULE_ROOT . "_module/autoload.php";
include_once USERBOT_MODULE_ROOT . "_module/function.php";
include_once USERBOT_MODULE_ROOT . "_module/sharding.php";

// инициализируем данные в вендоре
\BaseFrame\Url\UrlHandler::init(DOMAIN_PIVOT);
\BaseFrame\Error\ErrorHandler::init(DISPLAY_ERRORS);
\BaseFrame\Socket\SocketHandler::init(getConfig("SOCKET_URL"), getConfig("SOCKET_MODULE"), ca_certificate: CA_CERTIFICATE);
\BaseFrame\Module\ModuleHandler::init(CURRENT_MODULE);
\BaseFrame\Crypt\CryptProvider::init([
	\BaseFrame\Crypt\CryptProvider::DEFAULT => new \BaseFrame\Crypt\CryptData(ENCRYPT_KEY_DEFAULT, ENCRYPT_IV_DEFAULT),
]);
\BaseFrame\Conf\ConfHandler::init(
	getConfig("SHARDING_GO"), getConfig("SHARDING_SPHINX"), getConfig("SHARDING_RABBIT"),
	getConfig("SHARDING_MYSQL"), getConfig("SHARDING_MCACHE"),getConfig("GLOBAL_OFFICE_IP")
);

// возвращаем обработчики
return include_once USERBOT_MODULE_ROOT . "_module/route.php";

// @formatter:on