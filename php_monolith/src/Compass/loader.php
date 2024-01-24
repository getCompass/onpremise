<?php

$dir = dirname(__FILE__);

// подключаем прямыми ссылками,
// чтобы не сканировать лишний раз директорию
$route_handler_map["Thread"]       = include_once $dir . "/Thread/_module.php";
$route_handler_map["Conversation"] = include_once $dir . "/Conversation/_module.php";
$route_handler_map["Company"]      = include_once $dir . "/Company/_module.php";
$route_handler_map["Speaker"]      = include_once $dir . "/Speaker/_module.php";
$route_handler_map["FileBalancer"] = include_once $dir . "/FileBalancer/_module.php";
$route_handler_map["Pivot"]        = include_once $dir . "/Pivot/_module.php";
$route_handler_map["Userbot"]      = include_once $dir . "/Userbot/_module.php";
$route_handler_map["Announcement"] = include_once $dir . "/Announcement/_module.php";

// возвращаем все обработчики путей для модуля
return $route_handler_map;
