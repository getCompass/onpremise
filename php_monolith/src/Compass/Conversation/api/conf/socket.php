<?php

namespace Compass\Conversation;

// конфиг с глобальными эндпоинтами (которые существуют вне DPC)
$CONFIG["SOCKET_URL"] = [
	"pivot"    => ENTRYPOINT_PIVOT,
	"company"  => ENTRYPOINT_DOMINO,
	"intercom" => ENTRYPOINT_INTERCOM,
];

// конфиг с query для обращения к определенному модулю
$CONFIG["SOCKET_MODULE"] = [
	"pivot"         => [
		"socket_path" => "/api/socket/pivot/",
	],
	"company"       => [
		"socket_path" => "/api/socket/company/",
	],
	"file_balancer" => [
		"socket_path" => "/api/socket/files/",
	],
	"thread"        => [
		"socket_path" => "/api/socket/thread/",
	],
	"speaker"       => [
		"socket_path" => "/api/socket/speaker/",
	],
	"intercom"      => [
		"socket_path" => "/api/socket/intercom/",
	],
];

$CONFIG["SOCKET_ALLOW_KEY"] = [

	"company"       => [
		"auth_type"     => \BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_COMPANY,
		"allow_methods" => [
			"conversations.updateProfileDataToSphinxGroupMemberList",
			"conversations.createPublicForUserId",
			"conversations.getLinkListFromText",
			"conversations.addRespect",
			"conversations.tryEditMessageText",
			"conversations.tryDeleteMessageList",
			"conversations.doAutoCommitWorkedHours",
			"conversations.getManagedByMapList",
			"conversations.getPublicHeroesConversationMap",
			"conversations.getConversationCardList",
			"conversations.addAchievement",
			"conversations.joinToGroupConversationList",
			"conversations.isUserCanSendInvitesInGroups",
			"conversations.addDismissalRequestMessage",
			"conversations.getConversationInfoList",
			"conversations.getHiringConversationUserIdList",
			"conversations.clearConversationsForUser",
			"conversations.checkClearConversationsForUser",
			"conversations.sendMessage",
			"conversations.sendMessageToUser",
			"conversations.getHiringRequestMessageMaps",
			"conversations.getDismissalRequestMessageMaps",
			"conversations.sendRemindMessage",
			"conversations.actualizeTestRemindForMessage",
			"invites.clearInvitesForUser",
			"invites.checkClearInvitesForUser",
			"groups.createHiringGroup",
			"groups.createCompanyDefaultGroups",
			"groups.addToDefaultGroups",
			"groups.getCompanyDefaultGroups",
			"groups.getCompanyHiringGroups",
			"groups.createCompanyExtendedEmployeeCardGroups",
			"groups.addUserbotToGroup",
			"groups.removeUserbotFromGroup",
			"groups.getUserbotGroupInfoList",
			"antispam.clearAll",
			"system.execCompanyUpdateScript",
			"system.sendMessageWithFile",
			"userbot.addReaction",
			"userbot.removeReaction",
			"intercom.addMessageFromSupportBot",
			"search.tryReindex",
			"groups.createRespectConversation",
			"groups.addMembersToRespectConversation",
		],
	],
	"thread"        => [
		"auth_type"              => \BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_NONE,
		"auth_key"               => "",
		"allowed_forward_errors" => [],
		"allow_methods"          => [
			"conversations.getUsers",
			"conversations.getUsersByConversationList",
			"conversations.addThreadToMessage",
			"conversations.getMetaForCreateThread",
			"conversations.getMessage",
			"conversations.getMessageList",
			"conversations.addRepostFromThread",
			"conversations.addRepostFromThreadV2",
			"conversations.getRepostMessages",
			"conversations.confirmThreadRepost",
			"conversations.addRepostFromThreadBatching",
			"conversations.isCanCommitWorkedHours",
			"conversations.tryCommitWorkedHoursFromThread",
			"conversations.tryExactingFromThread",
			"conversations.getConversationType",
			"conversations.getMessageData",
			"conversations.getMessageDataForSendRemind",
			"conversations.getHiringConversationMap",
			"conversations.getConversationDataForCreateThreadInHireRequest",
			"conversations.hideThreadForUser",
			"conversations.revealThread",
			"conversations.addThreadFileListToConversation",
			"conversations.addThreadFileListToHiringConversation",
			"conversations.hideThreadFileList",
			"conversations.deleteThreadFileList",
			"conversations.updateThreadsUpdatedData",
			"conversations.getThreadsUpdatedVersion",
			"conversations.getDynamicForThread",
			"conversations.createRemindOnMessage",
			"conversations.attachPreview",
			"conversations.deletePreviewList",
			"conversations.hidePreviewList",
		],
	],
	"speaker"       => [
		"auth_type"     => \BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_SPEAKER,
		"allow_methods" => [
			"conversations.checkIsAllowedForCall",
			"conversations.addCallMessage",
			"conversations.doRead",
		],
	],
	"go_event"      => [
		"auth_type"     => \BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_GO_EVENT,
		"allow_methods" => [
			"event.processEvent",
			"event.processEventList",
			"task.processList",
		],
	],
	"userbot"       => [
		"auth_type"     => \BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_KEY,
		"auth_key"      => COMPANY_USERBOT_PRIVATE_KEY,
		"allow_methods" => [
			"userbot.sendMessageToUser",
			"userbot.sendMessageToGroup",
			"userbot.addReaction",
			"userbot.removeReaction",
		],
	],
	"intercom"      => [
		"auth_type"     => \BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_INTERCOM,
		"allow_methods" => [
			"intercom.addMessage",
			"intercom.deleteMessageList",
		],
	],
	"file_balancer" => [
		"auth_type"     => \BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_FILE_BALANCER,
		"allow_methods" => [
			"file.index",
			"file.reindex",
		],
	],
	"pivot"         => [
		"auth_type"     => \BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_SSL,
		"auth_key"      => PIVOT_TO_COMPANY_PUBLIC_KEY,
		"allow_methods" => [
			"intercom.getUserSupportConversationKey",
			"groups.createRespectConversation",
			"groups.addMembersToRespectConversation",
		],
	],
];

return $CONFIG;
