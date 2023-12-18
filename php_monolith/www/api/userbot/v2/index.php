<?php

// main
require_once __DIR__ . "/../../../../start.php";

// получаем переданные параметры
$payload = fromJson(file_get_contents("php://input"));

// начинаем работу
showAjax(Application\Entrypoint\ApiV2::processRequest("Userbot", get("api_method"), $payload));
