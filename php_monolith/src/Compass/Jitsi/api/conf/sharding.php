<?php

namespace Compass\Jitsi;

// -------------------------------------------------------
// mysql
// -------------------------------------------------------

// массив описывает подключние и работу с базой mysql
// в качестве ключа используется сокращение alias от полного названия базы данных (для удобства)
//
// например:
// 	sharding::pdo("main")-> вернет установленное соединение с базой данных которая написана в поле db
//
// ключ schemas:
// 	описывает поля каждой таблицы для каждой базы данных
//	возможно это показывается избыточностью, однако хорошо дисциплинирует командную разработку
// 	на внесение изменений в конфиги и в файл миграции SQL после изменения структуры таблицы
//
// важно!
//	юнит-тесты codeception не проходят, если фактическая структура базы отличается от описаной в эта файле
//	по-этому важно сразу после изменения базы в редакторе вносить изменения сюда и в файл миграции SQL
//

##########################################################
# endregion
##########################################################

$CONFIG["SHARDING_MYSQL"] = [

	"jitsi_data" => [
		"db"      => "jitsi_data",
		"mysql"   => [
			"host" => MYSQL_JITSI_DATA_HOST,
			"user" => MYSQL_JITSI_DATA_USER,
			"pass" => MYSQL_JITSI_DATA_PASS,
			"ssl"  => MYSQL_JITSI_DATA_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
			],
		],
	],

	"pivot_system"      => [
		"db"      => "pivot_system",
		"mysql"   => [
			"host" => MYSQL_SYSTEM_HOST,
			"user" => MYSQL_SYSTEM_USER,
			"pass" => MYSQL_SYSTEM_PASS,
			"ssl"  => MYSQL_SYSTEM_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"antispam_ip"    => "ip_address,key,is_stat_sent,count,expires_at",
				"antispam_phone" => "phone_number_hash,key,is_stat_sent,count,expires_at",
				"datastore"      => "key,extra",
				"auto_increment" => "key,value",
			],
		],
	],

	// база данных, хранящая компании
	"pivot_company_10m" => [
		"db"      => "pivot_company_10m",
		"mysql"   => [
			"host" => MYSQL_COMPANY_HOST,
			"user" => MYSQL_COMPANY_USER,
			"pass" => MYSQL_COMPANY_PASS,
			"ssl"  => MYSQL_COMPANY_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"company_user_list_1"  => "company_id,user_id,created_at,updated_at,extra",
				"company_user_list_2"  => "company_id,user_id,created_at,updated_at,extra",
				"company_user_list_3"  => "company_id,user_id,created_at,updated_at,extra",
				"company_user_list_4"  => "company_id,user_id,created_at,updated_at,extra",
				"company_user_list_5"  => "company_id,user_id,created_at,updated_at,extra",
				"company_user_list_6"  => "company_id,user_id,created_at,updated_at,extra",
				"company_user_list_7"  => "company_id,user_id,created_at,updated_at,extra",
				"company_user_list_8"  => "company_id,user_id,created_at,updated_at,extra",
				"company_user_list_9"  => "company_id,user_id,created_at,updated_at,extra",
				"company_user_list_10" => "company_id,user_id,created_at,updated_at,extra",
				"company_list_1"       => "user_id,company_id,is_has_pin,order,entry_id,created_at,updated_at,extra",
				"company_list_2"       => "user_id,company_id,is_has_pin,order,entry_id,created_at,updated_at,extra",
				"company_list_3"       => "user_id,company_id,is_has_pin,order,entry_id,created_at,updated_at,extra",
				"company_list_4"       => "user_id,company_id,is_has_pin,order,entry_id,created_at,updated_at,extra",
				"company_list_5"       => "user_id,company_id,is_has_pin,order,entry_id,created_at,updated_at,extra",
				"company_list_6"       => "user_id,company_id,is_has_pin,order,entry_id,created_at,updated_at,extra",
				"company_list_7"       => "user_id,company_id,is_has_pin,order,entry_id,created_at,updated_at,extra",
				"company_list_8"       => "user_id,company_id,is_has_pin,order,entry_id,created_at,updated_at,extra",
				"company_list_9"       => "user_id,company_id,is_has_pin,order,entry_id,created_at,updated_at,extra",
				"company_list_10"      => "user_id,company_id,is_has_pin,order,entry_id,created_at,updated_at,extra",
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

$CONFIG["SHARDING_GO"] = [
	"pivot_cache"     => [
		"host" => GO_PIVOT_CACHE_GRPC_HOST,
		"port" => GO_PIVOT_CACHE_GRPC_PORT,
	],
	"sender_balancer" => [
		"host" => GO_SENDER_BALANCER_GRPC_HOST,
		"port" => GO_SENDER_BALANCER_GRPC_PORT,
		"url"  => PUBLIC_WEBSOCKET_PIVOT,
	],
	"event"           => [
		"host" => GO_EVENT_HOST,
		"port" => GO_EVENT_PORT,
	],
];

$CONFIG["SHARDING_MCACHE"] = [
	"host" => MCACHE_HOST,
	"port" => MCACHE_PORT,
];

return $CONFIG;