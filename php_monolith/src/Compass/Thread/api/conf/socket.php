<?php

namespace Compass\Thread;

// конфиг с глобальными эндпоинтами
$CONFIG["SOCKET_URL"] = [
	"pivot"   => ENTRYPOINT_PIVOT,
	"company" => ENTRYPOINT_DOMINO,
];

// конфиг с query для обращения к определенному модулю
$CONFIG["SOCKET_MODULE"] = [
	"pivot"         => [
		"socket_path" => "/api/socket/pivot/",
	],
	"company"       => [
		"socket_path" => "/api/socket/company/",
	],
	"conversation"  => [
		"socket_path" => "/api/socket/conversation/",
	],
	"file_balancer" => [
		"socket_path" => "/api/socket/files/",
	],
];

$CONFIG["SOCKET_ALLOW_KEY"] = [
	"thread"       => [
		"auth_type"     => \BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_NONE,
		"auth_key"      => "",
		"allow_methods" => [
			"global.getStatus",
			"threads.attachUserToThread",
			"threads.setThreadAsUnfollow",
			"threads.followThread",
			"threads.doFollowUserListOnThread",
			"threads.setMuteFlag",
		],
	],
	"admin"        => [
		"auth_type"     => \BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_ADMIN,
		"allow_methods" => [
			"system.tryPing",
			"system.setConfig",
			"system.getConfig",
			"admin.setMessageSystemDeleted",
		],
	],
	"conversation" => [
		"auth_type"              => \BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_NONE,
		"auth_key"               => "",
		"allow_methods"          => [
			"threads.clearConversationForUserIdList",
			"threads.setRepostRelDeleted",
			"threads.setThreadIsReadOnly",
			"threads.setParentMessageIsDeleted",
			"threads.setParentMessageIsDeletedByThreadMapList",
			"threads.setParentMessageIsHiddenOrUnhiddenByThreadMapList",
			"threads.doClearMetaThreadCache",
			"threads.doClearParentMessageCache",
			"threads.doClearParentMessageListCache",
			"threads.doUnfollowThreadList",
			"threads.doUnfollowThreadListByConversationMap",
			"threads.getThreadListForFeed",
			"threads.getThreadListForBatchingFeed",
			"threads.addThreadForHiringRequest",
			"threads.addThreadForDismissalRequest",
		],
	],
	"test"         => [
		"auth_type"     => \BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_TEST,
		"allow_methods" => [
			"tests.sendTypingToThread",
			"tests.sendThreadMessage",
			"tests.sendThreadMessageAndDelete",
			"tests.sendQuoteThreadMessage",
			"tests.sendRepostFromThread",
			"tests.changeThreadMessageCreatedAt",
			"tests.getThreadMessagePushData",
			"tests.doSocketApiCheck",
			"tests.getTotalUnreadCount",
		],
	],
	"go_event"     => [
		"auth_type"     => \BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_GO_EVENT,
		"allow_methods" => [
			"event.processEvent",
			"event.processEventList",
			"task.processList",
		],
	],
	"company"      => [
		"auth_type"     => \BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_COMPANY,
		"allow_methods" => [
			"antispam.clearAll",
			"threads.doUnfollowThreadListIfRoleChangeToEmployee",
			"threads.addSystemMessageOnHireRequestStatusChanged",
			"threads.clearThreadsForUser",
			"threads.checkClearThreadsForUser",
			"threads.addSystemMessageToDismissalRequestThread",
			"threads.sendRemindMessage",
			"threads.actualizeTestRemindForMessage",
			"system.execCompanyUpdateScript",
			"threads.setParentMessageIsDeletedByThreadMapList",
			"system.setCompanyStatus",
			"userbot.sendMessageToThread",
		],
	],
	"userbot"       => [
		"auth_type"     => \BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_KEY,
		"auth_key"      => COMPANY_USERBOT_PRIVATE_KEY,
		"allow_methods" => [
			"userbot.sendMessageToThread",
			"userbot.addReaction",
			"userbot.removeReaction",
		],
	],
];

return $CONFIG;
