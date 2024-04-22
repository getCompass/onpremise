<?php

// main
require_once __DIR__ . "/../../../../start.php";

showAjax([
	"status"   => "ok",
	"response" => [
		"server_uid" => \Compass\Premise\SERVER_UID,
	],
	"server_time" => time(),
]);
