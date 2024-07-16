<?php

namespace Compass\Federation;

$CONFIG["SOCKET_URL"] = [];

// конфиг с query для обращения к определенному модулю
$CONFIG["SOCKET_MODULE"] = [];

$CONFIG["SOCKET_ALLOW_KEY"] = [
	"pivot" => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_PIVOT,
		"allow_methods" => [
			"sso.validateAuthToken",
			"sso.createUserRelationship",
			"sso.hasUserRelationship",
		],
	],
];

return $CONFIG;