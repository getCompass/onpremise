<?php

/**
 * Крон для проверки подсетей
 */

use BaseFrame\Server\ServerProvider;

const IS_CRON = true;
require_once __DIR__ . "/../../../../../../start.php";

// не стартуем на онпреме и тестовых
if (ServerProvider::isOnPremise() || ServerProvider::isTest()) {
	return;
}

$bot = new \Compass\Pivot\Cron_Subnet_Checker([
	"rabbit" => [
		"producer" => [
			"bot0",
			"bot1",
			"bot2",
			"bot3",
			"bot4",
			"bot5",
			"bot6",
			"bot7",
			"bot8",
			"bot9",
		],
	],
]);
$bot->start();