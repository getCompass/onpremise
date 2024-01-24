<?php

/**
 * Крон для выполнения задач в компании
 */

const IS_CRON = true;
require_once __DIR__ . "/../../../../../../start.php";

$bot = new \Compass\Pivot\Cron_Company_Task();
$bot->start();