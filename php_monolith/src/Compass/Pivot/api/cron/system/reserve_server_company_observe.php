<?php

/**
 * Крон для выполнения observe компаний для резервных серверов
 */

const IS_CRON = true;
require_once __DIR__ . "/../../../../../../start.php";

$bot = new \Compass\Pivot\Cron_System_ReserveServerCompanyObserve();
$bot->start();