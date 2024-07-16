<?php

// main
require_once __DIR__ . "/../../../../start.php";

// получаем данные запроса
$event_data = fromJson(file_get_contents("php://input"));

// обрабатываем запрос
showAjax(\Compass\Jitsi\JitsiEvent_Handler::handle($event_data));