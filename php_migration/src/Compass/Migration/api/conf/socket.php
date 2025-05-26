<?php

namespace Compass\Migration;

// конфиг с глобальными эндпоинтами
$CONFIG["SOCKET_URL"] = [
	"pivot" => ENTRYPOINT_PIVOT,
];

// конфиг с query для обращения к определенному модулю
$CONFIG["SOCKET_MODULE"] = [
	"pivot"        => [
		"socket_path" => "/api/socket/pivot/",
	],
];

return $CONFIG;