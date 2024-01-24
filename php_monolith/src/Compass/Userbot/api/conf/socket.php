<?php

namespace Compass\Userbot;

// конфиг с глобальными эндпоинтами
$CONFIG["SOCKET_URL"] = [
	"pivot" => ENTRYPOINT_PIVOT,
];

// конфиг с query для обращения к определенному модулю
$CONFIG["SOCKET_MODULE"] = [
	"company"       => [
		"socket_path" => "/api/socket/company/",
	],
	"conversation"  => [
		"socket_path" => "/api/socket/conversation/",
	],
	"thread"        => [
		"socket_path" => "/api/socket/thread/",
	],
	"file_balancer" => [
		"socket_path" => "/api/socket/files/",
	],
	"pivot"         => [
		"socket_path" => "/api/socket/pivot/",
	],
];

$CONFIG["SOCKET_ALLOW_KEY"] = [
	"company" => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => COMPANY_USERBOT_PRIVATE_KEY,
		"allow_methods" => [
			"userbot.sendCommand",
		],
	],
];

return $CONFIG;
