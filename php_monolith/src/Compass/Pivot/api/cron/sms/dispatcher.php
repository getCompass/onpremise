<?php

/**
 * Крон для отправки смс-сообщений и отслеживания их статуса отправки
 */

const IS_CRON = true;
require_once __DIR__ . "/../../../../../../start.php";

$bot = new \Compass\Pivot\Cron_Sms_Dispatcher();
$bot->start();