<?php

namespace Compass\Speaker;

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
//	по-этому важно сразу после изменения базы в редакторе вносить изменения сюда и в файл миграции

$CONFIG["SHARDING_MYSQL"] = [];

if (COMPANY_ID > 0) {

	$company_mysql      = getCompanyConfig("COMPANY_MYSQL");
	$company_mysql_host = $company_mysql["host"] ?? "";
	$company_mysql_port = $company_mysql["port"] ?? "";
	$company_mysql_user = $company_mysql["user"] ?? "";
	$company_mysql_pass = $company_mysql["pass"] ?? "";

	$CONFIG["SHARDING_MYSQL"] = [

		// база данных с системной информацией
		"company_system" => [
			"db"      => "company_system",
			"mysql"   => [
				"host" => $company_mysql_host . ":" . $company_mysql_port,
				"user" => $company_mysql_user,
				"pass" => $company_mysql_pass,
				"ssl"  => false,
			],
			"schemas" => [
				"antispam_user" => "user_id,key,is_stat_sent,count,expires_at",
				"datastore"     => "key,extra",
			],
		],

		"company_data" => [
			"db"      => "company_data",
			"mysql"   => [
				"host" => $company_mysql_host . ":" . $company_mysql_port,
				"user" => $company_mysql_user,
				"pass" => $company_mysql_pass,
				"ssl"  => false,
			],
			"schemas" => [],
		],

		// база данных с информацией о звонках
		"company_call" => [
			"db"      => "company_call",
			"mysql"   => [
				"host" => $company_mysql_host . ":" . $company_mysql_port,
				"user" => $company_mysql_user,
				"pass" => $company_mysql_pass,
				"ssl"  => false,
			],
			"schemas" => [
				"call_meta"                            => "meta_id,creator_user_id,is_finished,type,created_at,started_at,finished_at,updated_at,extra,users",
				"call_history"                         => "user_id,call_map,type,creator_user_id,created_at,started_at,finished_at,updated_at",
				"call_monitoring_dialing"              => "user_id,call_map,need_work,error_count,created_at",
				"call_monitoring_establishing_connect" => "call_map,user_id,need_work,error_count,created_at",
				"call_tester_queue"                    => "test_id,status,need_work,stage,error_count,created_at,updated_at,finished_at,extra",
				"janus_connection_list"                => "session_id,handle_id,user_id,publisher_user_id,connection_uuid,status,quality_state,is_publisher,is_send_video,is_send_audio,is_use_relay,publisher_upgrade_count,node_id,audio_packet_loss,video_packet_loss,audio_bad_quality_counter,video_bad_quality_counter,audio_loss_counter,video_loss_counter,last_ping_at,created_at,updated_at,participant_id,room_id,call_map",
				"janus_room"                           => "room_id,call_map,node_id,bitrate,session_id,handle_id,created_at,updated_at",
				"analytic_list"                        => "call_map,user_id,report_call_id,reconnect_count,middle_quality_count,created_at,updated_at,task_id,last_row_id",
				"analytic_queue"                       => "task_id,call_map,user_id,need_work,error_count,created_at",
				"report_connection_list"               => "report_id,call_map,call_id,user_id,status,created_at,updated_at,reason,extra",
				"call_ip_last_connection_issue"        => "ip_address_int,last_happened_at,created_at",
			],
		],
	];
}

// -------------------------------------------------------
// SPHINX
// -------------------------------------------------------

$CONFIG["SHARDING_SPHINX"] = [];

// -------------------------------------------------------
// GOLANG
// -------------------------------------------------------

// go_session - предпалагает sharding
$CONFIG["SHARDING_GO"] = [

	"sender"          => [
		"host" => GO_SENDER_GRPC_HOST,
		"port" => GO_SENDER_GRPC_PORT,
		"url"  => GO_SENDER_URL,
	],
	"company"         => [
		"host" => GO_COMPANY_GRPC_HOST,
		"port" => GO_COMPANY_GRPC_PORT,
	],
	"company_cache"   => [
		"host" => GO_COMPANY_CACHE_GRPC_HOST,
		"port" => GO_COMPANY_CACHE_GRPC_PORT,
	],
	"event"           => [
		"host" => GO_EVENT_GRPC_HOST,
		"port" => GO_EVENT_GRPC_PORT,
	],
	"collector_agent" => [
		"protocol" => GO_COLLECTOR_AGENT_PROTOCOL,
		"host"     => GO_COLLECTOR_AGENT_HOST,
		"port"     => GO_COLLECTOR_AGENT_HTTP_PORT,
	],
	"rating"          => [
		"host" 	=> GO_RATING_GRPC_HOST,
		"port" 	=> GO_RATING_GRPC_PORT,
	],
];

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

$CONFIG["SHARDING_MCACHE"] = [
	"host" => MCACHE_HOST,
	"port" => MCACHE_PORT,
];

return $CONFIG;