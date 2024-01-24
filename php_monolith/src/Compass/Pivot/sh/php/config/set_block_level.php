<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

require_once __DIR__ . "/../../../../../../start.php";

// устанавливаем уровень блокировки для онпремайза
if (ServerProvider::isOnPremise()) {

	$config = Type_System_Config::init()->getConf(Type_Antispam_Leveled_Main::CONFIG_BLOCK_LEVEL_KEY);
	if (count($config) === []) {
		Type_Antispam_Leveled_Main::setBlockLevel(Type_Antispam_Leveled_Main::MEDIUM_LEVEL);
	}
}