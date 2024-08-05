<?php

namespace Compass\Federation;

$CONFIG["SOCKET_URL"] = [
	"pivot" => ENTRYPOINT_PIVOT,
];

// конфиг с query для обращения к определенному модулю
$CONFIG["SOCKET_MODULE"] = [
	"pivot" => [
		"socket_path" => "/api/socket/pivot/",
	],
];

$CONFIG["SOCKET_ALLOW_KEY"] = [
	"pivot"    => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_PIVOT,
		"allow_methods" => [
			"oidc.validateAuthToken",
			"oidc.createUserRelationship",
			"oidc.hasUserRelationship",
			"ldap.validateAuthToken",
			"ldap.createUserRelationship",
			"ldap.hasUserRelationship",
			"sso.hasUserRelationship",
			"sso.deleteUserRelationship",
		],
	],
	"go_event" => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_GO_EVENT,
		"allow_methods" => [
			"event.processEvent",
			"event.processEventList",
			"task.processList",
		],
	],
];

return $CONFIG;