<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

require_once __DIR__ . "/../../../../../../start.php";

if (ServerProvider::isOnPremise()) {

	Type_App_Config::update();
}