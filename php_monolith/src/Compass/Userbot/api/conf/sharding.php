<?php

namespace Compass\Userbot;
use shardingConf;

// -------------------------------------------------------
// mysql
// -------------------------------------------------------

// массив описывает подключние и работу с базой mysql
// в качестве ключа испоьлзуется сокращение alias от полного названия базы данных (для удобства)
//
// например:
// 	ShardingGateway::database("main")-> вернет установленное соединение с базой данных которая написана в поле db
//
// ключ schemas:
// 	описывает поля каждой таблицы для каждой базы данных
//	возможно это показывается избыточностью, однако хорошо дисциплинирует командную разработку
// 	на внесение изменений в конфиги и в файл active.sql после изменения структуры таблицы
//
// важно!
//	юнит-тесты codeception не проходят, если фактическая структура базы отличается от описаной в эта файле
//	по-этому важно сразу после изменения базы в редакторе вносить изменения сюда и в файл active.sql
//

##########################################################
# endregion
##########################################################

$CONFIG["SHARDING_MYSQL"] = [

	##########################################################
	# region userbot_main - база данных ботов
	##########################################################

	// база данных ботов
	"userbot_main"    => [
		"db"      => "userbot_main",
		"mysql"   => [
			"host" => MYSQL_HOST,
			"user" => MYSQL_USER,
			"pass" => MYSQL_PASS,
			"ssl"  => false,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"request_list"  => "request_id,token,status,error_count,need_work,created_at,updated_at,request_data,result_data",
				"command_queue" => "task_id,error_count,need_work,created_at,params",
			],
		],
	],

	# endregion userbot_main
	##########################################################

	##########################################################
	# region userbot_service - сервисная база данных
	##########################################################

	// база данных ботов
	"userbot_service" => [
		"db"      => "userbot_service",
		"mysql"   => [
			"host" => MYSQL_HOST,
			"user" => MYSQL_USER,
			"pass" => MYSQL_PASS,
			"ssl"  => false,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"antispam_ip" => "ip_address,key,count,expire",
			],
		],
	],

	# endregion userbot_main
	##########################################################
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
	"userbot_cache" => [
		"host" => GO_USERBOT_CACHE_GRPC_HOST,
		"port" => GO_USERBOT_CACHE_GRPC_PORT,
	],
];

$CONFIG["SHARDING_MCACHE"] = [
	"host" => MCACHE_HOST,
	"port" => MCACHE_PORT,
];

return $CONFIG;
