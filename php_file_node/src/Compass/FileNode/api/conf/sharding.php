<?php

namespace Compass\FileNode;

// -------------------------------------------------------
// mysql
// -------------------------------------------------------

// массив описывает подключние и работу с базой mysql
// в качестве ключа испоьлзуется сокращение alias от полного названия базы данных (для удобства)
//
// например:
// 	\sharding::pdo("main")-> вернет установленное соединение с базой данных которая написана в поле db
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

	// база данных с системной информацией
	"system_file_node" => [
		"db"      => "system_file_node",
		"mysql"   => [
			"host" => MYSQL_SYSTEM_HOST,
			"user" => MYSQL_SYSTEM_USER,
			"pass" => MYSQL_SYSTEM_PASS,
			"ssl"  => false,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"datastore" => "key,extra",
			],
		],
	],

	##########################################################
	# region file_node - база данных, хранящая информацию о загруженных файлах
	##########################################################

	"file_node" => [
		"db"      => "file_node",
		"mysql"   => [
			"host" => MYSQL_FILE_NODE_HOST,
			"user" => MYSQL_FILE_NODE_USER,
			"pass" => MYSQL_FILE_NODE_PASS,
			"ssl"  => MYSQL_FILE_NODE_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"file"                        => "file_key,file_type,file_source,is_deleted,is_cdn,created_at,updated_at,size_kb,access_count,last_access_at,user_id,file_hash,mime_type,file_name,file_extension,part_path,extra",
				"datastore"                   => "key,extra",
				"post_upload_queue"           => "queue_id,file_type,error_count,need_work,file_key,part_path,extra",
				"relocate_queue"              => "file_key,error_count,need_work",
				"file_delete_by_expire_queue" => "file_key,file_source,file_type,is_finished,created_at,updated_at,need_work,error_count",
				"unit_test"                   => "key,int_row,extra",
			],
		],
	],

	##########################################################
	# endregion
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
	"collector_agent" => [
		"protocol" => GO_COLLECTOR_AGENT_PROTOCOL,
		"host"     => GO_COLLECTOR_AGENT_HOST,
		"port"     => GO_COLLECTOR_AGENT_HTTP_PORT,
	],
];

$CONFIG["SHARDING_MCACHE"] = [
	"host" => MCACHE_HOST,
	"port" => MCACHE_PORT,
];

return $CONFIG;