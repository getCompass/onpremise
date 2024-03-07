<?php

/**
 * Крон для отправки электронных писем
 */

const IS_CRON = true;
require_once __DIR__ . "/../../../../../../start.php";

$bot = new \Compass\Pivot\Cron_Mail_Dispatcher();
$bot->start();