<?php

namespace Compass\Speaker;

// конфиг с глобальными эндпоинтами
$CONFIG["SOCKET_URL"] = [
	"pivot"   => ENTRYPOINT_PIVOT,
	"company" => ENTRYPOINT_DOMINO,
];

// конфиг с query для обращения к определенному модулю
$CONFIG["SOCKET_MODULE"] = [
	"file_balancer" => [
		"socket_path" => "/api/socket/files/",
	],
	"conversation"  => [
		"socket_path" => "/api/socket/conversation/",
	],
	"thread"        => [
		"socket_path" => "/api/socket/thread/",
	],
	"speaker"       => [
		"socket_path" => "/api/socket/speaker/",
	],
	"company"       => [
		"socket_path" => "/api/socket/company/",
	],
	"admin"         => [
		"socket_path" => "/api/socket/",
	],
	"pivot"         => [
		"socket_path" => "/api/socket/pivot/",
	],
	"collector"     => [
		"socket_path" => "/api/socket/collector/",
	],
];

$CONFIG["SOCKET_ALLOW_KEY"] = [

	"pivot"   => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_SSL,
		"auth_key"      => PIVOT_TO_COMPANY_PUBLIC_KEY,
		"allow_methods" => [
			"company.calls.getUserLastCall",
			"company.member.setLastCall",
		],
	],
	"company" => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_COMPANY,
		"allow_methods" => [
			"antispam.clearAll",
			"system.execCompanyUpdateScript",
			"system.setCompanyStatus",
		],
	],
	"admin"   => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_ADMIN,
		"allow_methods" => [
			"system.tryPing",
			"system.setConfig",
		],
	],

	"conversation" => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_CONVERSATION,
		"allow_methods" => [
			"calls.doFinishSingleCall",
			"calls.getBatchingInfo",
		],

	],

	"test" => [
		"key"                  => SOCKET_KEY_TEST,
		"allow_methods_global" => [
			"tests.getWebrtcConnectionState",
			"tests.doAcceptCall",
			"tests.tryInitCall",
			"tests.tryEstablishCall",
			"tests.doSwitchCallMedia",
			"tests.doCallReconnect",
			"tests.doHangUp",
			"tests.setDialingExpiry",
			"tests.setMonitoringEstablishingConnectExpiry",
			"tests.getCallExtraInfo",
			"tests.getCallPushData",
			"tests.doSocketApiCheck",
			"tests.getRoomBitrate",
			"tests.doFinishActiveCalls",
			"tests.doFinishActiveCallForUser",
			"tests.addTaskForTestCall",
			"tests.getCallTesterTask",
		],
		"allow_methods_local"  => [
			"tests.getWebrtcConnectionState",
			"tests.doAcceptCall",
			"tests.tryInitCall",
			"tests.tryEstablishCall",
			"tests.doSwitchCallMedia",
			"tests.doCallReconnect",
			"tests.doHangUp",
			"tests.setDialingExpiry",
			"tests.setMonitoringEstablishingConnectExpiry",
			"tests.getCallExtraInfo",
			"tests.getCallPushData",
			"tests.doSocketApiCheck",
			"tests.getRoomBitrate",
			"tests.doFinishActiveCalls",
			"tests.doFinishActiveCallForUser",
			"tests.addTaskForTestCall",
			"tests.getCallTesterTask",
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