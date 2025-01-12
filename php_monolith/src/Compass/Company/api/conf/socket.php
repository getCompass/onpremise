<?php

namespace Compass\Company;

// конфиг с глобальными эндпоинтами
$CONFIG["SOCKET_URL"] = [
	"pivot"        => ENTRYPOINT_PIVOT,
	"company"      => ENTRYPOINT_DOMINO,
	"announcement" => ENTRYPOINT_ANNOUNCEMENT,
	"userbot"      => ENTRYPOINT_USERBOT,
	"intercom"     => ENTRYPOINT_INTERCOM,
];

// конфиг с query для обращения к определенному модулю
$CONFIG["SOCKET_MODULE"] = [
	"pivot"        => [
		"socket_path" => "/api/socket/pivot/",
	],
	"conversation" => [
		"socket_path" => "/api/socket/conversation/",
	],
	"thread"       => [
		"socket_path" => "/api/socket/thread/",
	],
	"files"        => [
		"socket_path" => "/api/socket/files/",
	],
	"speaker"      => [
		"socket_path" => "/api/socket/speaker/",
	],
	"announcement" => [
		"socket_path" => "/api/socket/announcement/",
	],
	"userbot"      => [
		"socket_path" => "/api/socket/userbot/",
	],
	"intercom"     => [
		"socket_path" => "/api/socket/intercom/",
	],
];

$CONFIG["SOCKET_ALLOW_KEY"] = [
	"pivot"         => [
		"auth_type"     => \BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_SSL,
		"auth_key"      => PIVOT_TO_COMPANY_PUBLIC_KEY,
		"allow_methods" => [
			"company.member.addCreator",
			"company.member.addByRole",
			"company.member.kick",
			"company.member.setMemberCount",
			"company.member.getMemberCount",
			"company.member.updateUserInfo",
			"company.member.updateMemberInfo",
			"company.member.logoutUserSessionList",
			"company.member.getListOfActiveMembersByDay",
			"company.member.logoutAll",
			"company.member.checkIsAllowedForHiringHistory",
			"company.member.getUserRoleList",
			"company.member.checkIsOwner",
			"company.member.checkCanEditSpaceSettings",
			"company.member.checkCanAttachSpace",
			"company.member.deleteUser",
			"company.member.getActivityCountList",
			"company.member.getUserInfo",
			"company.member.setPermissions",
			"company.invite.doDecline",
			"company.invite.doDeclineList",
			"company.invite.doRevoke",
			"company.invite.doAccept",
			"company.invite.storeSentInvitations",
			"company.config.setExtendedEmployeeCard",
			"company.invite.getCountOfActiveInvites",
			"hiring.invitelink.accept",
			"hiring.invitelink.getInfo",
			"hiring.invitelink.getCreatorUserId",
			"hiring.invitelink.deleteAllByUser",
			"hiring.invitelink.getForAutoJoin",
			"hiring.hiringrequest.revoke",
			"company.task.doTask",
			"company.main.doActionsOnCreateCompany",
			"company.main.changeCompanyCreatedAt",
			"company.main.getCompanyConfigStatus",
			"system.clearTables",
			"system.getKickedUserList",
			"system.execCompanyUpdateScript",
			"company.main.delete",
			"system.purgeCompanyStepOne",
			"system.purgeCompanyStepTwo",
			"system.checkReadyCompany",
			"system.hibernate",
			"system.awake",
			"system.relocateNotice",
			"system.relocateStart",
			"system.relocateEnd",
			"company.userbot.doCommand",
			"system.getActionCount",
			"system.sendMessageWithFile",
			"system.addRemindBot",
			"system.addSystemBot",
			"system.addOperator",
			"system.updateRemindBot",
			"system.updateSystemBot",
			"premium.updatePremiumStatuses",
			"premium.onInvoiceCreated",
			"premium.onInvoicePayed",
			"premium.onInvoiceCanceled",
			"space.member.updatePermissions",
			"space.member.isMediaConferenceCreatingAllowed",
			"space.member.checkIsAllowedForCall",
			"space.member.incConferenceMembershipRating",
			"system.getCompanyAnalytic",
			"space.tariff.publishAnnouncement",
			"space.tariff.disableAnnouncements",
			"space.tariff.checkIsUnblocked",
			"space.getInfoForPurchase",
			"space.getEventCountInfo",
			"intercom.addMessageFromSupportBot",
			"hiring.joinlink.add",
			"hiring.joinlink.getInfoForMember",
			"space.search.tryReindex",
		],
	],

	"premise"       => [
		"auth_type"     => \BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_SSL,
		"auth_key"      => PIVOT_TO_COMPANY_PUBLIC_KEY,
		"allow_methods" => [
			"company.member.getAll",
		],
	],
	"conversation"  => [
		"auth_type"     => \BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_CONVERSATION,
		"allow_methods" => [
			"company.member.getUserRoleList",
			"employeecard.entity.addExactingness",
			"employeecard.entity.attachLinkListToEntity",
			"employeecard.entity.deleteCardEntityList",
			"employeecard.entity.setMessageMapListForExactingnessList",
			"employeecard.workedhours.doCommit",
			"employeecard.workedhours.doAppendFixedMessageMap",
			"employeecard.workedhours.tryDelete",
			"employeecard.workedhours.getWorkedHoursById",
			"company.main.exists",
			"company.config.getConfigByKey",
			"hiring.hiringrequest.getRequestDataForScript",
			"hiring.hiringrequest.setMessageMap",
			"company.userbot.getUserbotIdByUserId",
			"company.userbot.getStatusByUserId",
			"company.userbot.kickFromGroup",
		],
	],
	"thread"        => [
		"auth_type"     => \BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_THREAD,
		"allow_methods" => [
			"hiring.hiringrequest.getRequestData",
			"hiring.hiringrequest.getRequestDataBatching",
			"hiring.hiringrequest.setThreadMap",
			"hiring.hiringrequest.getMetaForCreateThread",
			"company.main.exists",
			"company.config.getConfigByKey",
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
	"go_company"    => [
		"auth_type"     => \BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_GO_COMPANY,
		"allow_methods" => [
			"system.getPivotSocketKey",
		],
	],
	"go_rating"     => [
		"auth_type"     => \BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_GO_RATING,
		"allow_methods" => [
			"system.getPivotSocketKey",
		],
	],
	"sender"        => [
		"auth_type"     => \BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_SENDER,
		"allow_methods" => [
			"system.getPivotSocketKey",
		],
	],
	"file_balancer" => [
		"auth_type"     => \BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_FILE_BALANCER,
		"allow_methods" => [
			"company.main.exists",
		],
	],
	"speaker"       => [
		"auth_type"     => \BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_SPEAKER,
		"allow_methods" => [
			"company.main.exists",
			"conversations.checkIsAllowedForCall",
			"conversations.addCallMessage",
		],
	],
	"userbot"       => [
		"auth_type"     => \BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_KEY,
		"auth_key"      => COMPANY_USERBOT_PRIVATE_KEY,
		"allow_methods" => [
			"company.userbot.getUserList",
			"company.userbot.updateCommandList",
			"company.userbot.getCommandList",
			"company.userbot.getGroupList",
		],
	],
	"partner"       => [
		"auth_type"     => \BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_PARTNER,
		"allow_methods" => [
			"company.member.getAll",
		],
	],
	"intercom"      => [
		"auth_type"     => \BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_INTERCOM,
		"allow_methods" => [
			"company.member.getUserInfo",
		],
	],
	"crm"      => [
		"auth_type"     => \BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_SSL,
		"auth_key"      => PIVOT_TO_COMPANY_PUBLIC_KEY,
		"allow_methods" => [
			"company.member.getAll",
		],
	],
	"go_file_auth" => [
		"auth_type"     => \BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_KEY,
		"auth_key"      => \SOCKET_KEY_GO_FILE_AUTH,
		"allow_methods" => [
			"space.member.checkSession",
		],
	]
];

return $CONFIG;