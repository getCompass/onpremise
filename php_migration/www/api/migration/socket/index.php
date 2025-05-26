<?php

// main
require_once __DIR__ . "/../../../../start.php";

if (!isset($_POST["payload"])) {
	throw new socketAccessException("No payload in socket method");
}

$payload = fromJson($_POST["payload"]);

if (!isset($payload["method"])) {
	throw new socketAccessException("No method in socket request");
}

showAjax(Socket_Handler::doStart($payload["method"], $payload, $payload["user_id"] ?? 0));
