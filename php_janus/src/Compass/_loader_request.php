<?php

$dir = dirname(__FILE__);

// подключаем прямыми ссылками,
// чтобы не сканировать лишний раз директорию
$route_handler_list[] = include_once $dir . "/Janus/_module_request.php";

// возвращаем все обработчики путей для модуля
return array_merge(...$route_handler_list);
