<?php

/**
 * Крон для выполнения тарифных задач в компании
 */

const IS_CRON = true;
require_once __DIR__ . "/../../../../../../start.php";

$bot = new \Compass\Pivot\Cron_Company_TariffPlanTask();
$bot->start();