<?php

namespace Compass\Premise;

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

	##########################################################
	# region premise_user - база данных с данными по пользователям сервера
	##########################################################

	"premise_user"      => [
		"db"      => "premise_user",
		"mysql"   => [
			"host" => MYSQL_HOST,
			"user" => MYSQL_USER,
			"pass" => MYSQL_PASS,
			"ssl"  => MYSQL_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"user_list"     => "user_id,npc_type_alias,space_status,has_premise_permissions,premise_permissions,created_at,updated_at,external_sso_id,external_other1_id,external_other2_id,external_data,extra",
				"space_list"    => "user_id,space_id,role_alias,permissions_alias,created_at,updated_at,extra",
				"space_counter" => "key,count,created_at,updated_at",
			],
		],
	],

	##########################################################
	# endregion
	##########################################################

	##########################################################
	# region premise_data - база данных с данными по серверу
	##########################################################

	// база данных, хранящая компании
	"premise_data"      => [
		"db"      => "premise_data",
		"mysql"   => [
			"host" => MYSQL_HOST,
			"user" => MYSQL_USER,
			"pass" => MYSQL_PASS,
			"ssl"  => MYSQL_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"premise_config" => "key,created_at,updated_at,value",
			],
		],
	],

	##########################################################
	# endregion
	##########################################################

	##########################################################
	# region premise_system - системная база данных
	##########################################################

	// база данных, хранящая файлы пивота
	"premise_system"    => [
		"db"      => "premise_system",
		"mysql"   => [
			"host" => MYSQL_HOST,
			"user" => MYSQL_USER,
			"pass" => MYSQL_PASS,
			"ssl"  => MYSQL_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"antispam_ip"   => "ip_address,key,is_stat_sent,count,expires_at",
				"antispam_user" => "user_id,key,is_stat_sent,count,expires_at",
			],
		],
	],

	##########################################################
	# region pivot_company - база данных, хранящая компании
	##########################################################

	// база данных, хранящая компании
	"pivot_company_10m" => [
		"db"      => "pivot_company_10m",
		"mysql"   => [
			"host" => MYSQL_HOST,
			"user" => MYSQL_USER,
			"pass" => MYSQL_PASS,
			"ssl"  => MYSQL_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"company_list_1"  => "company_id,is_deleted,status,created_at,updated_at,deleted_at,avatar_color_id,created_by_user_id,partner_id,domino_id,name,url,avatar_file_map,extra",
				"company_list_2"  => "company_id,is_deleted,status,created_at,updated_at,deleted_at,avatar_color_id,created_by_user_id,partner_id,domino_id,name,url,avatar_file_map,extra",
				"company_list_3"  => "company_id,is_deleted,status,created_at,updated_at,deleted_at,avatar_color_id,created_by_user_id,partner_id,domino_id,name,url,avatar_file_map,extra",
				"company_list_4"  => "company_id,is_deleted,status,created_at,updated_at,deleted_at,avatar_color_id,created_by_user_id,partner_id,domino_id,name,url,avatar_file_map,extra",
				"company_list_5"  => "company_id,is_deleted,status,created_at,updated_at,deleted_at,avatar_color_id,created_by_user_id,partner_id,domino_id,name,url,avatar_file_map,extra",
				"company_list_6"  => "company_id,is_deleted,status,created_at,updated_at,deleted_at,avatar_color_id,created_by_user_id,partner_id,domino_id,name,url,avatar_file_map,extra",
				"company_list_7"  => "company_id,is_deleted,status,created_at,updated_at,deleted_at,avatar_color_id,created_by_user_id,partner_id,domino_id,name,url,avatar_file_map,extra",
				"company_list_8"  => "company_id,is_deleted,status,created_at,updated_at,deleted_at,avatar_color_id,created_by_user_id,partner_id,domino_id,name,url,avatar_file_map,extra",
				"company_list_9"  => "company_id,is_deleted,status,created_at,updated_at,deleted_at,avatar_color_id,created_by_user_id,partner_id,domino_id,name,url,avatar_file_map,extra",
				"company_list_10" => "company_id,is_deleted,status,created_at,updated_at,deleted_at,avatar_color_id,created_by_user_id,partner_id,domino_id,name,url,avatar_file_map,extra",
			],
		],
	],

	##########################################################
	# endregion
	##########################################################

	##########################################################
	# region pivot_user_{10m} - база данных, хранящая пользователей
	##########################################################

	// база данных, хранящая пользователей

	"pivot_user_10m" => [
		"db"      => "pivot_user_10m",
		"mysql"   => [
			"host" => MYSQL_HOST,
			"user" => MYSQL_USER,
			"pass" => MYSQL_PASS,
			"ssl"  => MYSQL_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [],
		],
	],

	"pivot_user_20m" => [
		"db"      => "pivot_user_20m",
		"mysql"   => [
			"host" => MYSQL_HOST,
			"user" => MYSQL_USER,
			"pass" => MYSQL_PASS,
			"ssl"  => MYSQL_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [],
		],
	],

	##########################################################
	# endregion
	##########################################################

	##########################################################
	# region pivot_system - база данных с системной информацией
	##########################################################

	"pivot_system" => [
		"db"      => "pivot_system",
		"mysql"   => [
			"host" => MYSQL_HOST,
			"user" => MYSQL_USER,
			"pass" => MYSQL_PASS,
			"ssl"  => MYSQL_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [],
		],
	],
];

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
];

return $CONFIG;