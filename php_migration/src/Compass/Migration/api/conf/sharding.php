<?php

namespace Compass\Migration;

$CONFIG["SHARDING_GO"]    = [
	"database_controller" => [
		"host" => GO_DATABASE_CONTROLLER_HOST,
		"port" => GO_DATABASE_CONTROLLER_PORT,
	],
];

return $CONFIG;