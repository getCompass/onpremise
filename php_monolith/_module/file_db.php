<?php

/**
 * получаем префикс для базы файлов
 *
 * @return string
 * @throws parseException
 */
function getFileDbPrefix():string {

	//
	if (defined("CURRENT_SERVER") && CURRENT_SERVER == Compass\FileBalancer\PIVOT_SERVER) {
		return "pivot";
	}

	//
	if (defined("CURRENT_SERVER") && CURRENT_SERVER == Compass\FileBalancer\CLOUD_SERVER) {
		return "company";
	}

	throw new parseException("trying to get file db prefix from undefined server");
}

/**
 * получаем название базы для файлов
 *
 * @param string $shard_id
 *
 * @return string
 * @throws parseException
 */
function getFileDbName(string $shard_id):string {

	//
	if (defined("CURRENT_SERVER") && CURRENT_SERVER == Compass\FileBalancer\PIVOT_SERVER) {
		return "pivot_file_{$shard_id}";
	}

	//
	if (defined("CURRENT_SERVER") && CURRENT_SERVER == Compass\FileBalancer\CLOUD_SERVER) {
		return "company_data";
	}

	throw new parseException("trying to get file db prefix from undefined server");
}

/**
 * получаем название таблицы для файлов
 *
 * @param int $table_id
 *
 * @return string
 * @throws parseException
 */
function getFileTableName(int $table_id):string {

	$table_name = "file_list";

	//
	if (defined("CURRENT_SERVER") && CURRENT_SERVER == Compass\FileBalancer\PIVOT_SERVER) {
		return "{$table_name}_{$table_id}";
	}

	//
	if (defined("CURRENT_SERVER") && CURRENT_SERVER == Compass\FileBalancer\CLOUD_SERVER) {
		return $table_name;
	}

	throw new parseException("trying to get file db prefix from undefined server");
}

/**
 * получаем шардинг для базы файлов
 *
 * @param string $mysql_host
 * @param string $mysql_user
 * @param string $mysql_pass
 * @param bool   $mysql_ssl
 *
 * @return array
 * @throws parseException
 * @long
 */
function getFileListShardingInfo(string $mysql_host, string $mysql_user, string $mysql_pass, bool $mysql_ssl):array {

	$mysql      = [
		"host" => $mysql_host,
		"user" => $mysql_user,
		"pass" => $mysql_pass,
		"ssl"  => $mysql_ssl,
	];
	$table_list = [
		"file_list" => "meta_id,file_type,file_source,node_id,is_deleted,created_at,updated_at,size_kb,user_id,file_hash,mime_type,file_name,file_extension,extra",
	];

	//
	if (defined("CURRENT_SERVER") && CURRENT_SERVER == Compass\FileBalancer\PIVOT_SERVER) {

		$output = [];
		for ($i = 2021; $i <= date("Y", time()); $i++) {

			$output["pivot_file_{$i}"] = [
				"db"      => "pivot_file_{$i}",
				"mysql"   => $mysql,
				"schemas" => [
					"sharding_info" => [
						"type" => shardingConf::SHARDING_TYPE_INT,
						"data" => shardingConf::makeDataForIntShardingType(1, 12),
					],
					"tables"        => $table_list,
				],
			];
		}

		return $output;
	}

	//
	if (defined("CURRENT_SERVER") && CURRENT_SERVER == Compass\FileBalancer\CLOUD_SERVER) {

		$table_list["file_list"] = "meta_id,year,month,file_type,file_source,node_id,is_deleted,created_at,updated_at,size_kb,user_id,file_hash,mime_type,file_name,file_extension,extra";
		return [
			"company_data" => [
				"db"      => "company_data",
				"mysql"   => $mysql,
				"schemas" => [
					"sharding_info" => [
						"type" => shardingConf::SHARDING_TYPE_NONE,
						"data" => [],
					],
					"tables"        => $table_list,
				],
			],
		];
	}

	throw new parseException("trying to get file db prefix from undefined server");
}
