<?php

// ----
// основной файл, которые инклудим при старте
// ----

// жесткая типизация
declare(strict_types = 1);

// часовой пояс сервера
const SERVER_TIMEZONE = "Europe/Moscow";
date_default_timezone_set(SERVER_TIMEZONE);

// locale
setlocale(LC_ALL, "en_US.utf8");
setlocale(LC_NUMERIC, "en_US.utf8");

$path_frame_work = __DIR__ . "/";

// основное
require_once $path_frame_work . "define.php";
require_once $path_frame_work . "system/functions.php";
require_once $path_frame_work . "system/sharding.php";
require_once $path_frame_work . "system/mcache.php";
require_once $path_frame_work . "system/rabbit.php";
require_once $path_frame_work . "system/curl.php";
require_once $path_frame_work . "system/shutdown_handler.php";
require_once $path_frame_work . "system/exception.php";
require_once $path_frame_work . "system/sharedmemory.php";
require_once $path_frame_work . "system/bus.php";
require_once $path_frame_work . "system/formatter.php";
require_once $path_frame_work . "system/grpc.php";
require_once $path_frame_work . "system/jwt.php";
require_once $path_frame_work . "system/route_handler.php";
require_once $path_frame_work . "system/config.php";
require_once $path_frame_work . "system/sharding_gateway.php";

require_once $path_frame_work . "cron/default.php";
require_once $path_frame_work . "entity/sanitizer.php";
require_once $path_frame_work . "entity/validator.php";
require_once $path_frame_work . "entity/validator/structure.php";

require_once $path_frame_work . "conf/flag.php";