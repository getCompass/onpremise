<?php

/**
 * Крон-наблюдатель за смс-провайдерами
 */

const IS_CRON = true;
require_once __DIR__ . "/../../../../../../../start.php";

$bot = new \Compass\Pivot\Cron_Sms_Provider_Observer();
$bot->start();