<?php

namespace Compass\FileBalancer;

// -------------------------------------------------------
// mysql
// -------------------------------------------------------

// массив описывает подключние и работу с базой mysql
// в качестве ключа испоьлзуется сокращение alias от полного названия базы данных (для удобства)
//
// например:
// 	sharding::pdo("main")-> вернет установленное соединение с базой данных которая написана в поле db
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

if (defined("CURRENT_SERVER") && CURRENT_SERVER == PIVOT_SERVER) {

	$mysql_host = MYSQL_FILE_HOST;
	$mysql_user = MYSQL_FILE_USER;
	$mysql_pass = MYSQL_FILE_PASS;
} else {

	$company_mysql      = COMPANY_ID > 0 ? getCompanyConfig("COMPANY_MYSQL") : [];
	$company_mysql_host = $company_mysql["host"] ?? "";
	$company_mysql_port = $company_mysql["port"] ?? "";
	$company_mysql_user = $company_mysql["user"] ?? "";
	$company_mysql_pass = $company_mysql["pass"] ?? "";

	$mysql_host = $company_mysql_host . ":" . $company_mysql_port;
	$mysql_user = $company_mysql_user;
	$mysql_pass = $company_mysql_pass;
}

$db_sharding_list = [

	// база данных с системной информацией
	"pivot_system"   => [
		"db"      => "pivot_system",
		"mysql"   => [
			"host" => $mysql_host,
			"user" => $mysql_user,
			"pass" => $mysql_pass,
			"ssl"  => false,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"auto_increment" => "key,value",
				"datastore"      => "key,extra",
				"antispam_user"  => "user_id,key,is_stat_sent,count,expires_at",
			],
		],
	],

	// база данных с системной информацией
	"company_system" => [
		"db"      => "company_system",
		"mysql"   => [
			"host" => $mysql_host,
			"user" => $mysql_user,
			"pass" => $mysql_pass,
			"ssl"  => false,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"auto_increment" => "key,value",
				"datastore"      => "key,extra",
				"antispam_user"  => "user_id,key,is_stat_sent,count,expires_at",
			],
		],
	],

	##########################################################
	# region pivot_file_{Y}|company_data.file_list - база данных, хранящая информацию о загруженных файлах
	##########################################################

	// берется из функции ниже массива

	##########################################################
	# endregion
	##########################################################
];

//
if (defined("CURRENT_SERVER") && CURRENT_SERVER == PIVOT_SERVER) {

	$db_file_list_sharding = getFileListShardingInfo(
		MYSQL_FILE_HOST,
		MYSQL_FILE_USER,
		MYSQL_FILE_PASS,
		MYSQL_FILE_SSL
	);
} else {

	$company_mysql      = COMPANY_ID > 0 ? getCompanyConfig("COMPANY_MYSQL") : [];
	$company_mysql_host = $company_mysql["host"] ?? "";
	$company_mysql_port = $company_mysql["port"] ?? "";
	$company_mysql_user = $company_mysql["user"] ?? "";
	$company_mysql_pass = $company_mysql["pass"] ?? "";

	$db_file_list_sharding = getFileListShardingInfo(
		$company_mysql_host . ":" . $company_mysql_port,
		$company_mysql_user,
		$company_mysql_pass,
		false
	);
}
$CONFIG["SHARDING_MYSQL"] = array_merge($db_sharding_list, $db_file_list_sharding);

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
	"pivot_cache"     => [
		"host" => defined("GO_PIVOT_CACHE_GRPC_HOST") ? GO_PIVOT_CACHE_GRPC_HOST : "",
		"port" => defined("GO_PIVOT_CACHE_GRPC_PORT") ? GO_PIVOT_CACHE_GRPC_PORT : "",
	],
	"company_cache"   => [
		"host" => defined("GO_COMPANY_CACHE_GRPC_HOST") ? GO_COMPANY_CACHE_GRPC_HOST : "",
		"port" => defined("GO_COMPANY_CACHE_GRPC_PORT") ? GO_COMPANY_CACHE_GRPC_PORT : "",
	],
	"collector_agent" => [
		"host" => defined("GO_COLLECTOR_AGENT_HOST") ? GO_COLLECTOR_AGENT_HOST : "",
		"port" => defined("GO_COLLECTOR_AGENT_HTTP_PORT") ? GO_COLLECTOR_AGENT_HTTP_PORT : "",
	],
	"sender"          => [
		"host" => defined("GO_SENDER_GRPC_HOST") ? GO_SENDER_GRPC_HOST : "",
		"port" => defined("GO_SENDER_GRPC_PORT") ? GO_SENDER_GRPC_PORT : "",
	],
];

$CONFIG["SHARDING_MCACHE"] = [
	"host" => MCACHE_HOST,
	"port" => MCACHE_PORT,
];

return $CONFIG;