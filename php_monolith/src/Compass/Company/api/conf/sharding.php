<?php

namespace Compass\Company;

// -------------------------------------------------------
// mysql
// -------------------------------------------------------

// массив описывает подключние и работу с базой mysql
// в качестве ключа используется сокращение alias от полного названия базы данных (для удобства)
//
// например:
// 	ShardingGateway::database("main")-> вернет установленное соединение с базой данных которая написана в поле db
//
// ключ schemas:
// 	описывает поля каждой таблицы для каждой базы данных
//	возможно это показывается избыточностью, однако хорошо дисциплинирует командную разработку
// 	на внесение изменений в конфиги и в файл миграции SQL после изменения структуры таблицы
//
// важно!
//	юнит-тесты codeception не проходят, если фактическая структура базы отличается от описаной в эта файле
//	по-этому важно сразу после изменения базы в редакторе вносить изменения сюда и в файл миграции S

##########################################################
# endregion
##########################################################

$CONFIG["SHARDING_MYSQL"] = [];

if (COMPANY_ID > 0) {

	$company_mysql      = getCompanyConfig("COMPANY_MYSQL");
	$company_mysql_host = $company_mysql["host"] ?? "";
	$company_mysql_port = $company_mysql["port"] ?? "";
	$company_mysql_user = $company_mysql["user"] ?? "";
	$company_mysql_pass = $company_mysql["pass"] ?? "";

	$CONFIG["SHARDING_MYSQL"] = [

		##########################################################
		# region company_data - база данных, хранящая основную информацию
		##########################################################

		// база данных
		"company_data"   => [
			"db"      => "company_data",
			"mysql"   => [
				"host" => $company_mysql_host . ":" . $company_mysql_port,
				"user" => $company_mysql_user,
				"pass" => $company_mysql_pass,
				"ssl"  => false,
			],
			"schemas" => [
				"company_dynamic"              => "key,value,created_at,updated_at",
				"premium_payment_request_list" => "requested_by_user_id,is_payed,requested_at,created_at,updated_at",
				"premium_payment_request_menu" => "user_id,requested_by_user_id,is_unread,created_at,updated_at",
				"entry_list"                   => "entry_id,entry_type,user_id,created_at",
				"entry_join_link_list"         => "entry_id,join_link_uniq,hiring_request_id,inviter_user_id,created_at",
				"exit_list"                    => "exit_task_id,user_id,status,step,created_at,updated_at,extra",
				"join_link_list"               => "join_link_uniq,entry_option,status,type,can_use_count,expires_at,creator_user_id,created_at,updated_at",
				"member_list"                  => "user_id,role,npc_type,permissions,created_at,updated_at,company_joined_at,left_at,full_name_updated_at,mbti_type,full_name,short_description,avatar_file_key,comment,extra",
				"userbot_list"                 => "userbot_id,status_alias,user_id,smart_app_name,created_at,updated_at,extra",
				"member_notification_list"     => "user_id,snoozed_until,created_at,updated_at,token,device_list,extra",
				"dismissal_request"            => "dismissal_request_id,status,created_at,updated_at,creator_user_id,dismissal_user_id,extra",
				"hiring_conversation_preset"   => "hiring_conversation_preset_id,status,creator_user_id,created_at,updated_at,title,conversation_list",
				"hiring_request"               => "hiring_request_id,status,join_link_uniq,entry_id,hired_by_user_id,created_at,updated_at,candidate_user_id,extra",
				"company_config"               => "key,created_at,updated_at,value",
				"session_active_list"          => "session_uniq,user_id,user_company_session_token,created_at,updated_at,login_at,ip_address,user_agent,extra",
				"session_history_list"         => "session_uniq,user_id,user_company_session_token,status,created_at,login_at,logout_at,ip_address,user_agent,extra",
				"member_menu"                  => "notification_id,user_id,action_user_id,type,is_unread,created_at,updated_at",
				"smart_app_list"               => "smart_app_id,catalog_item_id,creator_user_id,smart_app_uniq_name,created_at,updated_at,extra",
				"smart_app_user_rel"           => "smart_app_id,user_id,status,created_at,updated_at,extra",
			],
		],

		##########################################################
		# endregion
		##########################################################

		##########################################################
		# region company_member - база данных, хранящая информацию об участниках
		##########################################################

		// база данных
		"company_member" => [
			"db"      => "company_member",
			"mysql"   => [
				"host" => $company_mysql_host . ":" . $company_mysql_port,
				"user" => $company_mysql_user,
				"pass" => $company_mysql_pass,
				"ssl"  => false,
			],
			"schemas" => [
				"usercard_achievement_list"  => "achievement_id,user_id,creator_user_id,type,is_deleted,created_at,updated_at,header_text,description_text,data",
				"usercard_dynamic"           => "user_id,created_at,updated_at,data",
				"usercard_exactingness_list" => "exactingness_id,user_id,creator_user_id,type,is_deleted,created_at,updated_at,data",
				"usercard_loyalty_list"      => "loyalty_id,user_id,creator_user_id,is_deleted,created_at,updated_at,comment_text,data",
				"usercard_month_plan_list"   => "row_id,user_id,type,plan_value,user_value,created_at,updated_at",
				"usercard_respect_list"      => "respect_id,user_id,creator_user_id,type,is_deleted,created_at,updated_at,respect_text,data",
				"usercard_sprint_list"       => "sprint_id,user_id,creator_user_id,is_success,is_deleted,started_at,end_at,created_at,updated_at,header_text,description_text,data",
				"usercard_worked_hour_list"  => "worked_hour_id,user_id,day_start_at,type,is_deleted,value_1000,created_at,updated_at,data",
				"usercard_member_rel"        => "row_id,user_id,role,recipient_user_id,is_deleted,created_at,updated_at",
				"security_pin_confirm_story" => "confirm_key,user_id,status,created_at,updated_at,expires_at",
				"security_pin_enter_history" => "try_enter_id,user_id,status,created_at,enter_pin_hash_version,enter_pin_hash,user_company_session_token",
				"security_list"              => "user_id,is_pin_required,created_at,updated_at,last_enter_pin_at,pin_hash_version,pin_hash",
			],
		],

		##########################################################
		# endregion
		##########################################################

		##########################################################
		# region company_system - база данных, хранящая системную информацию
		##########################################################

		// база данных
		"company_system" => [
			"db"      => "company_system",
			"mysql"   => [
				"host" => $company_mysql_host . ":" . $company_mysql_port,
				"user" => $company_mysql_user,
				"pass" => $company_mysql_pass,
				"ssl"  => false,
			],
			"schemas" => [
				"observer_member"      => "user_id,need_work,created_at,updated_at,data",
				"antispam_user"        => "user_id,key,is_stat_sent,count,expires_at",
				"datastore"            => "key,extra",
				"member_activity_list" => "user_id,day_start_at",
			],
		],

		##########################################################
		# endregion
		##########################################################
	];
}

// -------------------------------------------------------
// RABBIT
// -------------------------------------------------------

$CONFIG["SHARDING_RABBIT"] = [
	"bus" => [
		"host" => RABBIT_HOST,
		"port" => RABBIT_PORT,
		"user" => RABBIT_USER,
		"pass" => RABBIT_PASS,
	],
];

$CONFIG["SHARDING_GO"] = [
	"company_cache"   => [
		"host" => GO_COMPANY_CACHE_GRPC_HOST,
		"port" => GO_COMPANY_CACHE_GRPC_PORT,
	],
	"company"         => [
		"host" => GO_COMPANY_GRPC_HOST,
		"port" => GO_COMPANY_GRPC_PORT,
	],
	"sender"          => [
		"host" => GO_SENDER_GRPC_HOST,
		"port" => GO_SENDER_GRPC_PORT,
	],
	"collector_agent" => [
		"host" => GO_COLLECTOR_AGENT_HOST,
		"port" => GO_COLLECTOR_AGENT_HTTP_PORT,
	],
	"event"           => [
		"host" => GO_EVENT_GRPC_HOST,
		"port" => GO_EVENT_GRPC_PORT,
	],
	"rating"          => [
		"host" => GO_RATING_GRPC_HOST,
		"port" => GO_RATING_GRPC_PORT,
	],
];

$CONFIG["SHARDING_MCACHE"] = [
	"host" => MCACHE_HOST,
	"port" => MCACHE_PORT,
];

return $CONFIG;