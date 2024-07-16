<?php

namespace Compass\Jitsi;

$CONFIG["SOCKET_URL"] = [
	"pivot" => ENTRYPOINT_PIVOT,
];

// конфиг с query для обращения к определенному модулю
$CONFIG["SOCKET_MODULE"] = [
	"files" => [
		"socket_path" => "/api/socket/files/",
	],
	"pivot" => [
		"socket_path" => "/api/socket/pivot/",
	],
];

$CONFIG["SOCKET_ALLOW_KEY"] = [
	"go_event" => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_GO_EVENT,
		"allow_methods" => [
			"task.processList",
		],
	],
];

return $CONFIG;