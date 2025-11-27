<?php

namespace Compass\Premise;

// конфиг с query для обращения к определенному модулю
$CONFIG["SOCKET_MODULE"] = [
	"company" => [
		"socket_path" => "/api/socket/company/",
	],
];

$CONFIG["SOCKET_ALLOW_KEY"] = [
	"go_event" => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_GO_EVENT,
		"allow_methods" => [
			"event.processEvent",
			"event.processEventList",
			"task.processList",
		],
	],
	"pusher"   => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_GO_PUSHER,
		"allow_methods" => [
			"premise.getServerInfo",
		],
	],
	"pivot"   => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_PIVOT,
		"allow_methods" => [
			"premise.userRegistered",
			"premise.setPermissions",
		],
	],
];

return $CONFIG;