<?php

// main
require_once __DIR__ . "/../../../../start.php";

$post_data = $_POST;

if (!isset($post_data["method"]) ||
	!isset($post_data["user_id"]) ||
	!isset($post_data["sender_module"]) ||
	!isset($post_data["json_params"]) ||
	!isset($post_data["signature"])) {

	throw new socketAccessException("No valid post_data in socket method");
}

showAjax(Application\Entrypoint\Socket::processRequest("Announcement", $_POST["method"], "announcement", $_POST, false));

