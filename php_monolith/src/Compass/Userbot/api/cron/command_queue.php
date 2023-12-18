<?php

declare(strict_types = 1);

namespace Compass\Userbot;

// ------------------------------------------
// крон для выполнения команд, отправленных боту в диалоге
// ------------------------------------------

define("IS_CRON", true);
require_once __DIR__ . "/../../../../../start.php";

$param = [];

$bot = new Cron_CommandQueue($param);
$bot->start();

