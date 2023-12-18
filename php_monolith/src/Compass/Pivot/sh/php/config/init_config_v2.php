<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

require_once __DIR__ . "/../../../../../../start.php";

$feature_v2 = new Domain_App_Entity_Feature_V2();
$rule_v2    = new Domain_App_Entity_Rule_V2();

$need_force_update = ServerProvider::isOnPremise();

// для каждой платформы и типа приложения (comteam, compass) генерим конфиги
foreach (\BaseFrame\System\UserAgent::getAvailablePlatformList() as $platform) {

	foreach (\BaseFrame\System\UserAgent::getAvailableAppNameList() as $app_name) {
		$feature_v2->initializeConfig($platform, $app_name, $need_force_update);
	}
}

$rule_v2->initializeConfig($need_force_update);
