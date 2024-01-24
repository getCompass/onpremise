<?php

// main
require_once __DIR__ . "/../../../../start.php";

if (!isset($_POST["json_params"])) {
	throw new socketAccessException("Not found json_params in socket request");
}

if (!isset($_POST["method"])) {
	throw new socketAccessException("Not found method in socket request");
}

if (!isset($_POST["company_id"])) {
	throw new socketAccessException("Not found company id in request");
}

$module = explode("/", $_SERVER["REQUEST_URI"])[3];

// начинаем работу
showAjax(Application\Entrypoint\Socket::processRequest("Conversation", $_POST["method"], "conversation", $_POST, false));