<?php

// ------------------------------------------
// базовый крон, которые выполняет общую работу раз в 1,5,15,30,60 минут
// ------------------------------------------

const IS_CRON = true;
require_once __DIR__ . "/../../../../../start.php";

$bot = new Compass\Premise\Cron_General();
$bot->start();