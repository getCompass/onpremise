<?php

$dir = dirname(__FILE__);

// подключаем прямыми ссылками,
// чтобы не сканировать лишний раз директорию
$route_handler_map["Migration"] = include_once $dir . "/Migration/_module.php";

// возвращаем все обработчики путей для модуля
return $route_handler_map;
