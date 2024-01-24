<?php

namespace Compass\FileBalancer;

// конфиг с глобальными эндпоинтами (которые существуют вне DPC)
$CONFIG["SOCKET_URL"] = [
	"pivot"   => ENTRYPOINT_PIVOT,
	"company" => ENTRYPOINT_DOMINO,
];

// конфиг с query для обращения к определенному модулю
$CONFIG["SOCKET_MODULE"] = [
	"pivot"   => [
		"socket_path" => "/api/socket/pivot/",
	],
	"company" => [
		"socket_path" => "/api/socket/company/",
	],
	"profile" => [
		"socket_path" => "/api/socket/pivot/",
	],
	"conversation" => [
		"socket_path" => "/api/socket/conversation/",
	],
];

$CONFIG["SOCKET_ALLOW_KEY"] = [

	"pivot"     => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_PIVOT,
		"allow_methods" => [
			"antispam.clearAll",
			"files.getFileList",
			"files.getNodeForUpload",
			"files.setFileListDeleted",
			"files.checkIsDeleted",
			"files.getFileByKeyList",
		],
	],
	"file_node" => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_FILE_NODE,
		"allow_methods" => [
			"files.setDeleted",
			"files.trySaveFile",
			"previews.doImageDownload",
			"files.doUpdateFile",
			"files.setContent",
		],
	],
	"company"   => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_COMPANY,
		"allow_methods" => [
			"antispam.clearAll",
			"system.setCompanyStatus",
			"files.getFileByKeyList",
		],
	],

	"test" => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_TEST,
		"allow_methods" => [
			"tests.doSocketApiCheck",
			"tests.setFileDeleted",
			"tests.setFileListDeleted",
			"tests.getFileNodeConfig",
			"tests.getTestFileKey",
		],
	],

	"conversation" => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_CONVERSATION,
		"allow_methods" => [
			"previews.getNodeForDownload",
			"files.getFileList",
			"files.getFileWithContentList",
		],
	],
	"thread"       => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_THREAD,
		"allow_methods" => [
			"previews.getNodeForDownload",
		],
	],
	"userbot"      => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => COMPANY_USERBOT_PRIVATE_KEY,
		"allow_methods" => [
			"files.getNodeForUserbot",
		],
	],
	"intercom"     => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_INTERCOM,
		"allow_methods" => [
			"files.getNodeForUpload",
			"files.getFileByKeyList",
		],
	],
	"crm"     => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_CRM,
		"allow_methods" => [
			"files.getFileList",
		],
	],
];

return $CONFIG;
