<?php

namespace Compass\FileNode;

// конфиг с глобальными эндпоинтами (которые существуют вне DPC)
$CONFIG["SOCKET_URL"] = [
	"pivot"   => ENTRYPOINT_PIVOT,
];

// конфиг с query для обращения к определенному модулю
$CONFIG["SOCKET_MODULE"] = [
	"pivot"         => [
		"socket_path" => "/api/socket/pivot/",
	],
	"file_balancer" => [
		"socket_path" => "/api/socket/files/",
	],
];

$CONFIG["SOCKET_ALLOW_KEY"] = [

	"pivot"         => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_PIVOT,
		"allow_methods" => [
			"nodes.uploadDefaultFile",
			"system.doUploadFile",
			"nodes.replaceUserbotAvatar",
			"nodes.replacePreviewForWelcomeVideo",
			"nodes.replaceResizedDefaultFile",
			"nodes.uploadInvoice",
			"nodes.uploadAvatarFile",
			"nodes.uploadFileByUrl",
		],
	],
	"file_node"     => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_ME,
		"allow_methods" => [
			"previews.doImageDownload",
		],
	],
	"file_balancer" => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_FILE_BALANCER,
		"allow_methods" => [
			"nodes.onUserBlockOpponent",
			"nodes.trySaveToken",
			"nodes.addToRelocateQueue",
			"nodes.getFileTypeRel",
			"nodes.doCropImage",
			"nodes.trySaveTokenForCrop",
		],
	],
	"conversation"  => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_CONVERSATION,
		"allow_methods" => [
			"previews.doImageDownload",
		],
	],
	"thread"        => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_THREAD,
		"allow_methods" => [
			"previews.doImageDownload",
		],
	],
	"intercom"      => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_INTERCOM,
		"allow_methods" => [
			"nodes.uploadAvatarFile",
			"intercom.doUploadFile",
		],
	],
];

return $CONFIG;