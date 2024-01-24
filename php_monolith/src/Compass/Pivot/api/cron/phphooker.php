<?php

// ------------------------------------------
// крон, для асинхронных выполнений функций
// ------------------------------------------

const IS_CRON = true;
require_once __DIR__ . "/../../../../../start.php";

$param = [
	"rabbit" => [
		"producer" => [
			"bot0",
		],
	],
];

$bot = new \Compass\Pivot\Cron_Phphooker($param);
$bot->start();

