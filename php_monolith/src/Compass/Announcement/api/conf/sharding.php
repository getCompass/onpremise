<?php

namespace Compass\Announcement;

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
//	по-этому важно сразу после изменения базы в редакторе вносить изменения сюда и в файл миграции SQL

$CONFIG["SHARDING_MYSQL"] = [
	"announcement_service"  => [
		"db"      => "announcement_service",
		"mysql"   => [
			"host" => MYSQL_HOST,
			"user" => MYSQL_USER,
			"pass" => MYSQL_PASS,
			"ssl"  => false,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"antispam_ip"     => "ip_address,key,is_stat_sent,count,expire",
				"antispam_user"   => "user_id,key,is_stat_sent,count,expire",
				"phphooker_queue" => "task_id,task_type,error_count,need_work,created_at,params",
				"datastore"       => "key,extra",
			],
		],
	],
	"announcement_main"     => [
		"db"      => "announcement_main",
		"mysql"   => [
			"host" => MYSQL_HOST,
			"user" => MYSQL_USER,
			"pass" => MYSQL_PASS,
			"ssl"  => false,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"announcement" => "announcement_id,is_global,status,company_id,expires_at,type,priority,created_at,updated_at,resend_repeat_time,receiver_user_id_list,excluded_user_id_list,extra",
			],
		],
	],
	"announcement_user_10m" => [
		"db"      => "announcement_user_10m",
		"mysql"   => [
			"host" => MYSQL_HOST,
			"user" => MYSQL_USER,
			"pass" => MYSQL_PASS,
			"ssl"  => false,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_INT,
				"data" => \shardingConf::makeDataForIntShardingType(0, 9),
			],
			"tables"        => [
				"user_announcement" => "announcement_id,user_id,is_read,created_at,updated_at,next_resend_at,resend_attempted_at,extra",
				"user_company"      => "user_id,company_id,expires_at,created_at,updated_at",
			],
		],
	],
	"announcement_user_20m" => [
		"db"      => "announcement_user_20m",
		"mysql"   => [
			"host" => MYSQL_HOST,
			"user" => MYSQL_USER,
			"pass" => MYSQL_PASS,
			"ssl"  => false,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_INT,
				"data" => \shardingConf::makeDataForIntShardingType(0, 9),
			],
			"tables"        => [
				"user_announcement" => "announcement_id,user_id,is_read,created_at,updated_at,next_resend_at,resend_attempted_at,extra",
				"user_company"      => "user_id,company_id,expires_at,created_at,updated_at",
			],
		],
	],
	"announcement_company"  => [
		"db"      => "announcement_company",
		"mysql"   => [
			"host" => MYSQL_HOST,
			"user" => MYSQL_USER,
			"pass" => MYSQL_PASS,
			"ssl"  => false,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_INT,
				"data" => \shardingConf::makeDataForIntShardingType(0, 9),
			],
			"tables"        => [
				"company_user" => "company_id,user_id,expires_at,created_at,updated_at",
			],
		],
	],
	"announcement_security" => [
		"db"      => "announcement_security",
		"mysql"   => [
			"host" => MYSQL_HOST,
			"user" => MYSQL_USER,
			"pass" => MYSQL_PASS,
			"ssl"  => false,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_INT,
				"data" => \shardingConf::makeDataForIntShardingType(0, 9),
			],
			"tables"        => [
				"token_user" => "user_id,bound_session_key,created_at,updated_at,expires_at,token",
			],
		],
	],
];

// -------------------------------------------------------
// RABBIT
// -------------------------------------------------------

$CONFIG["SHARDING_RABBIT"] = [
	"local" => [
		"host" => RABBIT_HOST,
		"port" => RABBIT_PORT,
		"user" => RABBIT_USER,
		"pass" => RABBIT_PASS,
	],
	"bus"   => [
		"host" => RABBIT_BUS_HOST,
		"port" => RABBIT_BUS_PORT,
		"user" => RABBIT_BUS_USER,
		"pass" => RABBIT_BUS_PASS,
	],
];

// -------------------------------------------------------
// Memcached
// -------------------------------------------------------

/** @var array точки доступа к go-сервисам */

$CONFIG["SHARDING_MCACHE"] = [
	"host" => MCACHE_HOST,
	"port" => MCACHE_PORT,
];

// -------------------------------------------------------
// Go-сервисы
// -------------------------------------------------------

/** @var array точки доступа к go-сервисам */
$CONFIG["SHARDING_GO"] = [

	"sender_balancer" => [
		"host" => GO_SENDER_BALANCER_GRPC_HOST,
		"port" => GO_SENDER_BALANCER_GRPC_PORT,
		"url"  => PUBLIC_WEBSOCKET_ANNOUNCEMENT,
	],
];

return $CONFIG;