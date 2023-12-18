<?php

// main
require_once __DIR__ . "/../../../../start.php";

// получаем переданные параметры
$payload = file_get_contents("php://input");

// начинаем работу
showAjax(Application\Entrypoint\ApiV3::processRequest("Userbot", get("api_method"), $payload));

