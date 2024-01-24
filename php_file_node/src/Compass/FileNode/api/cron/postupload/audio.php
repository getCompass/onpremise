<?php

// ------------------------------------------
// крон который выполняет пост обработку файла
// ------------------------------------------

const IS_CRON = true;
require_once __DIR__ . "/../../../../../../start.php";

$bot = new \Compass\FileNode\Cron_Postupload_Audio([
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

// это очень нужно V
ini_set("memory_limit", "512M");

$bot->start();

