<?php declare(strict_types = 1);

// -------------------------------------------
// КОНСТАНТЫ И КОНФИГИ
// -------------------------------------------

// устанавливаем пути
define("PATH_ROOT", dirname(__FILE__) . "/");

const PATH_API  = PATH_ROOT . "api/";
const PATH_TEMP = PATH_ROOT . "cache/";
const PATH_LOGS = PATH_ROOT . "logs/";
const PATH_WWW  = PATH_ROOT . "www/";
const PATH_TMP  = "/tmp/";

const CODE_UNIQ_VERSION = 103000;

// пути к подпроектам
const PATH_COMPASS_MODULE = PATH_ROOT . "src/Compass/";
const SERVICE_NAME        = "file_node";

$cron_extra_path = defined("IS_CRON") && IS_CRON === true ? "cron/" : "";

const CONFIG_LOG_CRON_PATH      = PATH_ROOT . "/logs/cron/";
const CONFIG_LOG_EXCEPTION_PATH = PATH_ROOT . "/logs/exception/";

define("LOG_ERROR_PHP", PATH_ROOT . "/logs/{$cron_extra_path}__php_error.log");
define("LOG_CRITICAL_PHP_EXCEPTION", PATH_ROOT . "/logs/{$cron_extra_path}__php_critical.log");
define("LOG_ERROR_MYSQL", PATH_ROOT . "/logs/{$cron_extra_path}__mysql_error.log");
define("LOG_ADMIN", PATH_ROOT . "/logs/{$cron_extra_path}__admin.log");

// подгружаем composer
spl_autoload_register(function(string $class_name) {

	$file = str_replace("\\", DIRECTORY_SEPARATOR, $class_name) . ".php";
	$path = PATH_ROOT . "src/" . $file;

	if (file_exists($path)) {

		require $path;
		return true;
	}

	return false;
});

// conf
$max_execution_time = 10;
ini_set("log_errors", "1");
ini_set("error_log", __DIR__ . "/logs/_error_start_php.log");
ini_set("display_errors", "0");
ini_set("error_reporting", (string) (E_ALL ^ E_DEPRECATED ^ E_STRICT));
ini_set("max_execution_time", (string) $max_execution_time);
ini_set("max_input_time", (string) $max_execution_time);
ini_set("memory_limit", "2G");
set_time_limit($max_execution_time);

// подгружаем composer
require PATH_ROOT . "src/Modules/vendor/autoload.php";

// инициализируем логи и необходимые системные параметры в вендорах
\BaseFrame\Path\PathHandler::init(
	PATH_ROOT, PATH_LOGS, CONFIG_LOG_CRON_PATH, CONFIG_LOG_EXCEPTION_PATH,
	LOG_ERROR_PHP, LOG_CRITICAL_PHP_EXCEPTION, LOG_ERROR_MYSQL, LOG_ADMIN, PATH_API
);

\BaseFrame\Conf\ConfBaseFrameHandler::init($max_execution_time, "UTF-8", "utf8mb4");

// headers
\BaseFrame\Conf\ConfBaseFrameProvider::setHeaderList([
	"Cache-Control: no-store, no-cache, must-revalidate",
	"Pragma: no-cache",
	"Content-type: text/html;charset=" . "utf8mb4",
]);

// загружаем глобальные конфиги
require_once PATH_ROOT . "private/custom.php";
require_once PATH_ROOT . "private/main.php";

// загружаем проекты
$route_handler_list = require_once PATH_COMPASS_MODULE . "_loader_request.php";

// регистрируем маршруты для всех модулей
foreach ($route_handler_list as $route_handler) {

	/** @var RouteHandler $route_handler */
	Application\Entrypoint\Resolver::instance()->registerRoutesHandler($route_handler->getType(), $route_handler, $route_handler->getServedRoutes());
}

// @formatter:on