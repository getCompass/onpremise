<?php

namespace Compass\Pivot;

$CONFIG["SOCKET_URL"] = [
	"pivot"            => ENTRYPOINT_PIVOT,
	"announcement"     => ENTRYPOINT_ANNOUNCEMENT,
	"userbot"          => ENTRYPOINT_USERBOT,
	"partner"          => ENTRYPOINT_PARTNER,
	"intercom"         => ENTRYPOINT_INTERCOM,
	"collector_server" => ENTRYPOINT_ANALYTIC,
	"federation"       => ENTRYPOINT_FEDERATION,
	"premise"          => ENTRYPOINT_PREMISE,
	"crm"              => ENTRYPOINT_PIVOT,
	"integration"      => ENTRYPOINT_INTEGRATION,
	"jitsi"            => ENTRYPOINT_PIVOT,
];

// конфиг с query для обращения к определенному модулю
$CONFIG["SOCKET_MODULE"] = [
	"pivot"         => [
		"socket_path" => "/api/socket/pivot/",
	],
	"company"       => [
		"socket_path" => "/api/socket/company/",
	],
	"files"         => [
		"socket_path" => "/api/socket/files/",
	],
	"announcement"  => [
		"socket_path" => "/api/socket/announcement/",
	],
	"userbot_cache" => [
		"socket_path" => "/api/socket/userbot_cache/",
	],
	"partner"       => [
		"socket_path" => "/api/socket/",
	],
	"billing"       => [
		"socket_path" => "/api/socket/",
	],
	"intercom"      => [
		"socket_path" => "/api/socket/intercom/",
	],
	"collector"     => [
		"socket_path" => "/api/socket/collector/",
	],
	"conversation"  => [
		"socket_path" => "/api/socket/conversation/",
	],
	"crm"           => [
		"socket_path" => "/api/socket/crm/",
	],
	"federation"    => [
		"socket_path" => "/api/socket/federation/",
	],
	"integration"   => [
		"socket_path" => "/api/socket/integration/",
	],
	"premise"       => [
		"socket_path" => "/api/socket/premise/",
	],
	"jitsi"         => [
		"socket_path" => "/api/socket/jitsi/",
	],
];

$CONFIG["SOCKET_ALLOW_KEY"] = [
	"company"          => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_SSL,
		"auth_key"      => "",
		"allow_methods" => [
			"company.auth.checkUserSessionToken",
			"company.profile.setName",
			"company.profile.setAvatar",
			"company.profile.setBaseInfo",
			"company.clearAvatar",
			"company.changeInfo",
			"company.delete",
			"pivot.company.ping",
			"pivot.security.doGenerateTwoFaToken",
			"pivot.security.tryValidateTwoFaToken",
			"pivot.security.setTwoFaTokenAsInactive",
			"company.user.getUserInfo",
			"company.user.getUserInfoList",
			"company.user.getBeforeRedesignUserInfo",
			"company.user.createInviteLink",
			"company.user.updateInviteLinkStatus",
			"company.member.isAlreadyInCompany",
			"company.member.getCreatorUserId",
			"company.member.doRejectHiringRequest",
			"company.member.doConfirmHiringRequest",
			"company.member.onUpgradeGuest",
			"notifications.clearTokenList",
			"company.notifications.setUserCompanyToken",
			"company.task.addScheduledCompanyTask",
			"company.member.kick",
			"company.system.startHibernate",
			"company.userbot.create",
			"company.userbot.edit",
			"company.userbot.refreshSecretKey",
			"company.userbot.refreshToken",
			"company.userbot.enable",
			"company.userbot.disable",
			"company.userbot.delete",
			"company.getAnalyticsInfo",
			"userbot.getInfo",
			"system.getScriptCompanyUserList",
			"system.getRootUserId",
			"tariff.increaseMemberCountLimit",
			"user.getScreenTimeStat",
		],
	],
	"migration"        => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_MIGRATION,
		"allow_methods" => [
			"system.getActiveCompanyIdList",
			"system.lockBeforeMigration",
			"system.unlockAfterMigration",
			"system.getActiveDominoCompanyIdList",
			"system.getDominoMigrationOptions",
			"company.notifications.setUserCompanyToken",
		],
	],
	"crm"              => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_CRM,
		"allow_methods" => [
			"pivot.company.getList",
			"partner.sendToGroupSupport",
		],
	],
	"admin"            => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_ADMIN,
		"allow_methods" => [
			"feature.getRawConfig",
			"feature.getFullConfig",
			"feature.get",
			"feature.addOrEdit",
			"feature.delete",
			"feature.changeName",
			"feature.addRule",
			"feature.deleteRule",
			"feature.editRule",
			"feature.getRuleList",
			"feature.getRule",
			"feature.attachRuleToFeature",
			"feature.detachRuleFromFeature",
			"pivot.antispam.getCurrentBlockLevel",
			"pivot.antispam.setBlockLevel",
			"company.getTierList",
			"company.relocateToAnotherDomino",
			"company.getRelocationList",
		],
	],
	"speaker"          => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_SSL,
		"auth_key"      => "",
		"allow_methods" => [
			"company.calls.getBusyUsers",
			"company.calls.getUserLastCall",
			"company.calls.setLastCall",
			"company.calls.getAllCompanyActiveCalls",
			"company.calls.tryMarkLineAsBusy",
		],
	],
	"pusher"           => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_PUSHER,
		"allow_methods" => [
			"user.getInfoForVoip",
			"system.getCompanySocketKey",
		],
	],
	"userbot_cache"    => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_USERBOT_CACHE,
		"allow_methods" => [
			"userbot.getInfo",
		],
	],
	"go_company"       => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_SSL,
		"auth_key"      => "",
		"allow_methods" => [
			"company.notifications.updateBadgeCount",
		],
	],
	"collector_server" => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_COLLECTOR,
		"allow_methods" => [
			"user.getUserCount",
		],
	],
	"stage"            => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_STAGE,
		"allow_methods" => [
			"pivot.stage.registrationUser",
			"userbot.getInfoLastCreated",
		],
	],
	"partner"          => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_PARTNER,
		"allow_methods" => [
			"partner.sendSms",
			"partner.resendSms",
		],
	],
	"userbot"          => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => GLOBAL_USERBOT_PRIVATE_KEY,
		"allow_methods" => [
			"userbot.setWebhookVersion",
		],
	],
	"billing"          => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_BILLING,
		"allow_methods" => [
			"partner.getUserAvatarFileLink",
			"user.getUserAvatarFileLinkList",
			"user.isAllowBatchPay",
			"partner.uploadInvoice",
			"partner.onInvoiceCreated",
			"partner.onInvoicePayed",
			"partner.onInvoiceCanceled",
			"partner.getFileByKeyList",
			"billing.assertGoods",
			"billing.isSpaceAdmin",
			"billing.activateSpacePurchase",
		],
	],
	"intercom"         => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_INTERCOM,
		"allow_methods" => [
			"intercom.createOrUpdateOperator",
			"intercom.getCompanyInfo",
			"intercom.getUserInfo",
			"intercom.addOperatorToCompany",
		],
	],
	"go_event"         => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_GO_EVENT,
		"allow_methods" => [
			"event.processEvent",
			"event.processEventList",
			"task.processList",
		],
	],
	"webstat"          => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_WEBSTAT,
		"allow_methods" => [
			"tariff.getFailedTaskList",
			"tariff.getStuckTaskList",
			"tariff.getFailedTaskHistory",
			"tariff.getFailedObserveList",
			"tariff.getStuckObserveList",
			"tariff.getAverageQueueTime",
			"tariff.increaseMemberCountLimit",
			"device.getDeviceLoginHistory",
		],
	],
	"go_rating"        => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_GO_RATING,
		"allow_methods" => [
			"rating.saveScreenTime",
			"rating.saveUserActionList",
			"rating.saveUserAnswerTime",
		],
	],
	"bothub"           => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_BOTHUB,
		"allow_methods" => [
			"bothub.getUserInfoByUserId",
			"bothub.getUserInfoByPhoneNumber",
			"bothub.getUserSpaceList",
			"bothub.getSpaceInfo",
			"bothub.getSpaceMemberList",
			"bothub.getBiggestUserSpace",
			"bothub.getUserSupportConversationKey",
			"bothub.getEventCountInfo",
		],
	],
	"www"              => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_WWW,
		"allow_methods" => [
			"attribution.onLandingVisit",
		],
	],
	"jitsi"            => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_JITSI,
		"allow_methods" => [
			"company.member.isMediaConferenceCreatingAllowed",
			"company.member.checkIsAllowedForCall",
			"user.getUsersIntersectSpaces",
			"user.incConferenceMembershipRating",
		],
	],
	"federation"       => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_FEDERATION,
		"allow_methods" => [
			"pivot.ldap.blockUserAuthentication",
			"pivot.ldap.kickUserFromAllCompanies",
			"pivot.ldap.unblockUserAuthentication",
			"pivot.ldap.isLdapAuthAvailable",
			"pivot.ldap.getUserInfo",
			"pivot.ldap.actualizeProfileData",
		],
	],
	"sender"           => [
		"auth_type"     => Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
		"auth_key"      => SOCKET_KEY_SENDER,
		"allow_methods" => [
			"user.validateSession",
		],
	],
];

return $CONFIG;