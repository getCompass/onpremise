<?php

/**
 * Крон для выполнения системных задач с компаниями
 */

const IS_CRON = true;
require_once __DIR__ . "/../../../../../../start.php";

// ------------------------------------------
// крон, для асинхронных выполнений функций
// ------------------------------------------

$param = [
	"rabbit" => [
		"producer" => [
			"bot0",
		],
	],
];

$bot = new \Compass\Pivot\Cron_Company_ServiceTask($param);
$bot->start();