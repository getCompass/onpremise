<?php

namespace Compass\Announcement;

/** @var array описание точек выхода */
$CONFIG["SOCKET_URL"] = [

	// ни с кем не общаемся
];

/** @var array описание точек входа */
$CONFIG["SOCKET_ALLOW_KEY"] = [

	"pivot" => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => GLOBAL_ANNOUNCEMENT_PRIVATE_KEY,
		"allow_methods" => [
			"announcement.publish",
			"announcement.disable",
			"announcement.registerToken",
			"announcement.bindUserToCompany",
			"announcement.unbindUserFromCompany",
			"announcement.invalidateUser",
			"announcement.getExistingTypeList",
		],
	],

	"company" => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => COMPANY_ANNOUNCEMENT_PRIVATE_KEY,
		"allow_methods" => [
			"announcement.publish",
			"announcement.disable",
			"announcement.bindUserToCompany",
			"announcement.unbindUserFromCompany",
			"announcement.changeReceiverUserList",
		],
	],

	"development" => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => GLOBAL_ANNOUNCEMENT_PRIVATE_KEY,
		"allow_methods" => [
			"announcement.publish",
			"announcement.disable",
		],
	],
];

return $CONFIG;