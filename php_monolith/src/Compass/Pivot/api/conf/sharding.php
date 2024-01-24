<?php

namespace Compass\Pivot;

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
	# region pivot_data - база данных с основной информацией
	##########################################################

	"pivot_data"        => [
		"db"      => "pivot_data",
		"mysql"   => [
			"host" => MYSQL_PIVOT_DATA_HOST,
			"user" => MYSQL_PIVOT_DATA_USER,
			"pass" => MYSQL_PIVOT_DATA_PASS,
			"ssl"  => MYSQL_PIVOT_DATA_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"checkpoint_phone_number_list" => "list_type,phone_number_hash,expires_at",
				"checkpoint_company_list"      => "list_type,company_id,expires_at",
				"pivot_config"                 => "key,value",
				"device_list_0"                => "device_id,user_id,created_at,updated_at,extra",
				"device_list_1"                => "device_id,user_id,created_at,updated_at,extra",
				"device_list_2"                => "device_id,user_id,created_at,updated_at,extra",
				"device_list_3"                => "device_id,user_id,created_at,updated_at,extra",
				"device_list_4"                => "device_id,user_id,created_at,updated_at,extra",
				"device_list_5"                => "device_id,user_id,created_at,updated_at,extra",
				"device_list_6"                => "device_id,user_id,created_at,updated_at,extra",
				"device_list_7"                => "device_id,user_id,created_at,updated_at,extra",
				"device_list_8"                => "device_id,user_id,created_at,updated_at,extra",
				"device_list_9"                => "device_id,user_id,created_at,updated_at,extra",
				"device_list_a"                => "device_id,user_id,created_at,updated_at,extra",
				"device_list_b"                => "device_id,user_id,created_at,updated_at,extra",
				"device_list_c"                => "device_id,user_id,created_at,updated_at,extra",
				"device_list_d"                => "device_id,user_id,created_at,updated_at,extra",
				"device_list_e"                => "device_id,user_id,created_at,updated_at,extra",
				"device_list_f"                => "device_id,user_id,created_at,updated_at,extra",
				"device_token_voip_list_0"     => "token_hash,user_id,created_at,updated_at,device_id",
				"device_token_voip_list_1"     => "token_hash,user_id,created_at,updated_at,device_id",
				"device_token_voip_list_2"     => "token_hash,user_id,created_at,updated_at,device_id",
				"device_token_voip_list_3"     => "token_hash,user_id,created_at,updated_at,device_id",
				"device_token_voip_list_4"     => "token_hash,user_id,created_at,updated_at,device_id",
				"device_token_voip_list_5"     => "token_hash,user_id,created_at,updated_at,device_id",
				"device_token_voip_list_6"     => "token_hash,user_id,created_at,updated_at,device_id",
				"device_token_voip_list_7"     => "token_hash,user_id,created_at,updated_at,device_id",
				"device_token_voip_list_8"     => "token_hash,user_id,created_at,updated_at,device_id",
				"device_token_voip_list_9"     => "token_hash,user_id,created_at,updated_at,device_id",
				"device_token_voip_list_a"     => "token_hash,user_id,created_at,updated_at,device_id",
				"device_token_voip_list_b"     => "token_hash,user_id,created_at,updated_at,device_id",
				"device_token_voip_list_c"     => "token_hash,user_id,created_at,updated_at,device_id",
				"device_token_voip_list_d"     => "token_hash,user_id,created_at,updated_at,device_id",
				"device_token_voip_list_e"     => "token_hash,user_id,created_at,updated_at,device_id",
				"device_token_voip_list_f"     => "token_hash,user_id,created_at,updated_at,device_id",
				"company_join_link_user_rel"   => "join_link_uniq,user_id,company_id,entry_id,status,created_at,updated_at",
				"company_join_link_rel_0"      => "join_link_uniq,company_id,status_alias,created_at,updated_at",
				"company_join_link_rel_1"      => "join_link_uniq,company_id,status_alias,created_at,updated_at",
				"company_join_link_rel_2"      => "join_link_uniq,company_id,status_alias,created_at,updated_at",
				"company_join_link_rel_3"      => "join_link_uniq,company_id,status_alias,created_at,updated_at",
				"company_join_link_rel_4"      => "join_link_uniq,company_id,status_alias,created_at,updated_at",
				"company_join_link_rel_5"      => "join_link_uniq,company_id,status_alias,created_at,updated_at",
				"company_join_link_rel_6"      => "join_link_uniq,company_id,status_alias,created_at,updated_at",
				"company_join_link_rel_7"      => "join_link_uniq,company_id,status_alias,created_at,updated_at",
				"company_join_link_rel_8"      => "join_link_uniq,company_id,status_alias,created_at,updated_at",
				"company_join_link_rel_9"      => "join_link_uniq,company_id,status_alias,created_at,updated_at",
				"company_join_link_rel_a"      => "join_link_uniq,company_id,status_alias,created_at,updated_at",
				"company_join_link_rel_b"      => "join_link_uniq,company_id,status_alias,created_at,updated_at",
				"company_join_link_rel_c"      => "join_link_uniq,company_id,status_alias,created_at,updated_at",
				"company_join_link_rel_d"      => "join_link_uniq,company_id,status_alias,created_at,updated_at",
				"company_join_link_rel_e"      => "join_link_uniq,company_id,status_alias,created_at,updated_at",
				"company_join_link_rel_f"      => "join_link_uniq,company_id,status_alias,created_at,updated_at",
			],
		],
	],

	##########################################################
	# endregion
	##########################################################

	##########################################################
	# region pivot_company - база данных, хранящая компании
	##########################################################

	// база данных, хранящая компании
	"pivot_company_10m" => [
		"db"      => "pivot_company_10m",
		"mysql"   => [
			"host" => MYSQL_PIVOT_COMPANY_HOST,
			"user" => MYSQL_PIVOT_COMPANY_USER,
			"pass" => MYSQL_PIVOT_COMPANY_PASS,
			"ssl"  => MYSQL_PIVOT_COMPANY_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"company_list_1"                 => "company_id,is_deleted,status,created_at,updated_at,deleted_at,avatar_color_id,created_by_user_id,partner_id,domino_id,name,url,avatar_file_map,extra",
				"company_list_2"                 => "company_id,is_deleted,status,created_at,updated_at,deleted_at,avatar_color_id,created_by_user_id,partner_id,domino_id,name,url,avatar_file_map,extra",
				"company_list_3"                 => "company_id,is_deleted,status,created_at,updated_at,deleted_at,avatar_color_id,created_by_user_id,partner_id,domino_id,name,url,avatar_file_map,extra",
				"company_list_4"                 => "company_id,is_deleted,status,created_at,updated_at,deleted_at,avatar_color_id,created_by_user_id,partner_id,domino_id,name,url,avatar_file_map,extra",
				"company_list_5"                 => "company_id,is_deleted,status,created_at,updated_at,deleted_at,avatar_color_id,created_by_user_id,partner_id,domino_id,name,url,avatar_file_map,extra",
				"company_list_6"                 => "company_id,is_deleted,status,created_at,updated_at,deleted_at,avatar_color_id,created_by_user_id,partner_id,domino_id,name,url,avatar_file_map,extra",
				"company_list_7"                 => "company_id,is_deleted,status,created_at,updated_at,deleted_at,avatar_color_id,created_by_user_id,partner_id,domino_id,name,url,avatar_file_map,extra",
				"company_list_8"                 => "company_id,is_deleted,status,created_at,updated_at,deleted_at,avatar_color_id,created_by_user_id,partner_id,domino_id,name,url,avatar_file_map,extra",
				"company_list_9"                 => "company_id,is_deleted,status,created_at,updated_at,deleted_at,avatar_color_id,created_by_user_id,partner_id,domino_id,name,url,avatar_file_map,extra",
				"company_list_10"                => "company_id,is_deleted,status,created_at,updated_at,deleted_at,avatar_color_id,created_by_user_id,partner_id,domino_id,name,url,avatar_file_map,extra",
				"company_user_list_1"            => "company_id,user_id,created_at,updated_at,extra",
				"company_user_list_2"            => "company_id,user_id,created_at,updated_at,extra",
				"company_user_list_3"            => "company_id,user_id,created_at,updated_at,extra",
				"company_user_list_4"            => "company_id,user_id,created_at,updated_at,extra",
				"company_user_list_5"            => "company_id,user_id,created_at,updated_at,extra",
				"company_user_list_6"            => "company_id,user_id,created_at,updated_at,extra",
				"company_user_list_7"            => "company_id,user_id,created_at,updated_at,extra",
				"company_user_list_8"            => "company_id,user_id,created_at,updated_at,extra",
				"company_user_list_9"            => "company_id,user_id,created_at,updated_at,extra",
				"company_user_list_10"           => "company_id,user_id,created_at,updated_at,extra",
				"company_tier_observe"           => "company_id,current_domino_tier,expected_domino_tier,need_work,created_at,updated_at,extra",
				"tariff_plan_1"                  => "id,space_id,type,plan_id,valid_till,active_till,free_active_till,created_at,option_list,payment_info,extra",
				"tariff_plan_2"                  => "id,space_id,type,plan_id,valid_till,active_till,free_active_till,created_at,option_list,payment_info,extra",
				"tariff_plan_3"                  => "id,space_id,type,plan_id,valid_till,active_till,free_active_till,created_at,option_list,payment_info,extra",
				"tariff_plan_4"                  => "id,space_id,type,plan_id,valid_till,active_till,free_active_till,created_at,option_list,payment_info,extra",
				"tariff_plan_5"                  => "id,space_id,type,plan_id,valid_till,active_till,free_active_till,created_at,option_list,payment_info,extra",
				"tariff_plan_6"                  => "id,space_id,type,plan_id,valid_till,active_till,free_active_till,created_at,option_list,payment_info,extra",
				"tariff_plan_7"                  => "id,space_id,type,plan_id,valid_till,active_till,free_active_till,created_at,option_list,payment_info,extra",
				"tariff_plan_8"                  => "id,space_id,type,plan_id,valid_till,active_till,free_active_till,created_at,option_list,payment_info,extra",
				"tariff_plan_9"                  => "id,space_id,type,plan_id,valid_till,active_till,free_active_till,created_at,option_list,payment_info,extra",
				"tariff_plan_10"                 => "id,space_id,type,plan_id,valid_till,active_till,free_active_till,created_at,option_list,payment_info,extra",
				"tariff_plan_history_1"          => "id,space_id,type,plan_id,valid_till,active_till,free_active_till,created_at,option_list,payment_info,extra",
				"tariff_plan_history_2"          => "id,space_id,type,plan_id,valid_till,active_till,free_active_till,created_at,option_list,payment_info,extra",
				"tariff_plan_history_3"          => "id,space_id,type,plan_id,valid_till,active_till,free_active_till,created_at,option_list,payment_info,extra",
				"tariff_plan_history_4"          => "id,space_id,type,plan_id,valid_till,active_till,free_active_till,created_at,option_list,payment_info,extra",
				"tariff_plan_history_5"          => "id,space_id,type,plan_id,valid_till,active_till,free_active_till,created_at,option_list,payment_info,extra",
				"tariff_plan_history_6"          => "id,space_id,type,plan_id,valid_till,active_till,free_active_till,created_at,option_list,payment_info,extra",
				"tariff_plan_history_7"          => "id,space_id,type,plan_id,valid_till,active_till,free_active_till,created_at,option_list,payment_info,extra",
				"tariff_plan_history_8"          => "id,space_id,type,plan_id,valid_till,active_till,free_active_till,created_at,option_list,payment_info,extra",
				"tariff_plan_history_9"          => "id,space_id,type,plan_id,valid_till,active_till,free_active_till,created_at,option_list,payment_info,extra",
				"tariff_plan_history_10"         => "id,space_id,type,plan_id,valid_till,active_till,free_active_till,created_at,option_list,payment_info,extra",
				"tariff_plan_observe"            => "space_id,observe_at,report_after,last_error_logs,created_at,updated_at",
				"tariff_plan_task"               => "id,space_id,type,status,need_work,created_at,updated_at,logs,extra",
				"tariff_plan_task_history"       => "id,space_id,type,status,in_queue_time,created_at,logs,extra",
				"tariff_plan_payment_history_1"  => "id,space_id,user_id,tariff_plan_id,payment_id,payment_at,created_at,updated_at",
				"tariff_plan_payment_history_2"  => "id,space_id,user_id,tariff_plan_id,payment_id,payment_at,created_at,updated_at",
				"tariff_plan_payment_history_3"  => "id,space_id,user_id,tariff_plan_id,payment_id,payment_at,created_at,updated_at",
				"tariff_plan_payment_history_4"  => "id,space_id,user_id,tariff_plan_id,payment_id,payment_at,created_at,updated_at",
				"tariff_plan_payment_history_5"  => "id,space_id,user_id,tariff_plan_id,payment_id,payment_at,created_at,updated_at",
				"tariff_plan_payment_history_6"  => "id,space_id,user_id,tariff_plan_id,payment_id,payment_at,created_at,updated_at",
				"tariff_plan_payment_history_7"  => "id,space_id,user_id,tariff_plan_id,payment_id,payment_at,created_at,updated_at",
				"tariff_plan_payment_history_8"  => "id,space_id,user_id,tariff_plan_id,payment_id,payment_at,created_at,updated_at",
				"tariff_plan_payment_history_9"  => "id,space_id,user_id,tariff_plan_id,payment_id,payment_at,created_at,updated_at",
				"tariff_plan_payment_history_10" => "id,space_id,user_id,tariff_plan_id,payment_id,payment_at,created_at,updated_at",
			],
		],
	],

	##########################################################
	# endregion
	##########################################################

	##########################################################
	# region pivot_file - база данных, хранящая файлы пивота
	##########################################################

	// база данных, хранящая файлы пивота
	"pivot_file_2021"   => [
		"db"      => "pivot_file_2021",
		"mysql"   => [
			"host" => MYSQL_PIVOT_DATA_HOST,
			"user" => MYSQL_PIVOT_DATA_USER,
			"pass" => MYSQL_PIVOT_DATA_PASS,
			"ssl"  => MYSQL_PIVOT_DATA_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_INT,
				"data" => \shardingConf::makeDataForIntShardingType(1, 12),
			],
			"tables"        => [
				"file_list" => "meta_id,file_type,file_source,is_deleted,is_cdn,node_id,created_at,updated_at,size_kb,user_id,file_hash,mime_type,file_name,file_extension,extra,content",
			],
		],
	],

	// база данных, хранящая файлы пивота
	"pivot_file_2022"   => [
		"db"      => "pivot_file_2022",
		"mysql"   => [
			"host" => MYSQL_PIVOT_DATA_HOST,
			"user" => MYSQL_PIVOT_DATA_USER,
			"pass" => MYSQL_PIVOT_DATA_PASS,
			"ssl"  => MYSQL_PIVOT_DATA_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_INT,
				"data" => \shardingConf::makeDataForIntShardingType(1, 12),
			],
			"tables"        => [
				"file_list" => "meta_id,file_type,file_source,is_deleted,is_cdn,node_id,created_at,updated_at,size_kb,user_id,file_hash,mime_type,file_name,file_extension,extra,content",
			],
		],
	],

	// база данных, хранящая файлы пивота
	"pivot_file_2023"   => [
		"db"      => "pivot_file_2023",
		"mysql"   => [
			"host" => MYSQL_PIVOT_DATA_HOST,
			"user" => MYSQL_PIVOT_DATA_USER,
			"pass" => MYSQL_PIVOT_DATA_PASS,
			"ssl"  => MYSQL_PIVOT_DATA_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_INT,
				"data" => \shardingConf::makeDataForIntShardingType(1, 12),
			],
			"tables"        => [
				"file_list" => "meta_id,file_type,file_source,is_deleted,is_cdn,node_id,created_at,updated_at,size_kb,user_id,file_hash,mime_type,file_name,file_extension,extra,content",
			],
		],
	],

	// база данных, хранящая файлы пивота
	"pivot_file_2024"   => [
		"db"      => "pivot_file_2024",
		"mysql"   => [
			"host" => MYSQL_PIVOT_DATA_HOST,
			"user" => MYSQL_PIVOT_DATA_USER,
			"pass" => MYSQL_PIVOT_DATA_PASS,
			"ssl"  => MYSQL_PIVOT_DATA_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_INT,
				"data" => \shardingConf::makeDataForIntShardingType(1, 12),
			],
			"tables"        => [
				"file_list" => "meta_id,file_type,file_source,is_deleted,is_cdn,node_id,created_at,updated_at,size_kb,user_id,file_hash,mime_type,file_name,file_extension,extra,content",
			],
		],
	],

	// база данных, хранящая файлы пивота
	"pivot_file_2025"   => [
		"db"      => "pivot_file_2025",
		"mysql"   => [
			"host" => MYSQL_PIVOT_DATA_HOST,
			"user" => MYSQL_PIVOT_DATA_USER,
			"pass" => MYSQL_PIVOT_DATA_PASS,
			"ssl"  => MYSQL_PIVOT_DATA_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_INT,
				"data" => \shardingConf::makeDataForIntShardingType(1, 12),
			],
			"tables"        => [
				"file_list" => "meta_id,file_type,file_source,is_deleted,is_cdn,node_id,created_at,updated_at,size_kb,user_id,file_hash,mime_type,file_name,file_extension,extra,content",
			],
		],
	],

	// база данных, хранящая файлы пивота
	"pivot_file_2026"   => [
		"db"      => "pivot_file_2026",
		"mysql"   => [
			"host" => MYSQL_PIVOT_DATA_HOST,
			"user" => MYSQL_PIVOT_DATA_USER,
			"pass" => MYSQL_PIVOT_DATA_PASS,
			"ssl"  => MYSQL_PIVOT_DATA_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_INT,
				"data" => \shardingConf::makeDataForIntShardingType(1, 12),
			],
			"tables"        => [
				"file_list" => "meta_id,file_type,file_source,is_deleted,is_cdn,node_id,created_at,updated_at,size_kb,user_id,file_hash,mime_type,file_name,file_extension,extra,content",
			],
		],
	],

	// база данных, хранящая файлы пивота
	"pivot_file_2027"   => [
		"db"      => "pivot_file_2027",
		"mysql"   => [
			"host" => MYSQL_PIVOT_DATA_HOST,
			"user" => MYSQL_PIVOT_DATA_USER,
			"pass" => MYSQL_PIVOT_DATA_PASS,
			"ssl"  => MYSQL_PIVOT_DATA_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_INT,
				"data" => \shardingConf::makeDataForIntShardingType(1, 12),
			],
			"tables"        => [
				"file_list" => "meta_id,file_type,file_source,is_deleted,is_cdn,node_id,created_at,updated_at,size_kb,user_id,file_hash,mime_type,file_name,file_extension,extra,content",
			],
		],
	],

	// база данных, хранящая файлы пивота
	"pivot_file_2028"   => [
		"db"      => "pivot_file_2028",
		"mysql"   => [
			"host" => MYSQL_PIVOT_DATA_HOST,
			"user" => MYSQL_PIVOT_DATA_USER,
			"pass" => MYSQL_PIVOT_DATA_PASS,
			"ssl"  => MYSQL_PIVOT_DATA_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_INT,
				"data" => \shardingConf::makeDataForIntShardingType(1, 12),
			],
			"tables"        => [
				"file_list" => "meta_id,file_type,file_source,is_deleted,is_cdn,node_id,created_at,updated_at,size_kb,user_id,file_hash,mime_type,file_name,file_extension,extra,content",
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
	"pivot_user_10m"    => [
		"db"      => "pivot_user_10m",
		"mysql"   => [
			"host" => MYSQL_PIVOT_USER_HOST,
			"user" => MYSQL_PIVOT_USER_USER,
			"pass" => MYSQL_PIVOT_USER_PASS,
			"ssl"  => MYSQL_PIVOT_USER_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"user_list_1"                        => "user_id,npc_type,invited_by_partner_id,invited_by_user_id,last_active_day_start_at,created_at,updated_at,full_name_updated_at,country_code,short_description,full_name,avatar_file_map,extra",
				"user_list_2"                        => "user_id,npc_type,invited_by_partner_id,invited_by_user_id,last_active_day_start_at,created_at,updated_at,full_name_updated_at,country_code,short_description,full_name,avatar_file_map,extra",
				"user_list_3"                        => "user_id,npc_type,invited_by_partner_id,invited_by_user_id,last_active_day_start_at,created_at,updated_at,full_name_updated_at,country_code,short_description,full_name,avatar_file_map,extra",
				"user_list_4"                        => "user_id,npc_type,invited_by_partner_id,invited_by_user_id,last_active_day_start_at,created_at,updated_at,full_name_updated_at,country_code,short_description,full_name,avatar_file_map,extra",
				"user_list_5"                        => "user_id,npc_type,invited_by_partner_id,invited_by_user_id,last_active_day_start_at,created_at,updated_at,full_name_updated_at,country_code,short_description,full_name,avatar_file_map,extra",
				"user_list_6"                        => "user_id,npc_type,invited_by_partner_id,invited_by_user_id,last_active_day_start_at,created_at,updated_at,full_name_updated_at,country_code,short_description,full_name,avatar_file_map,extra",
				"user_list_7"                        => "user_id,npc_type,invited_by_partner_id,invited_by_user_id,last_active_day_start_at,created_at,updated_at,full_name_updated_at,country_code,short_description,full_name,avatar_file_map,extra",
				"user_list_8"                        => "user_id,npc_type,invited_by_partner_id,invited_by_user_id,last_active_day_start_at,created_at,updated_at,full_name_updated_at,country_code,short_description,full_name,avatar_file_map,extra",
				"user_list_9"                        => "user_id,npc_type,invited_by_partner_id,invited_by_user_id,last_active_day_start_at,created_at,updated_at,full_name_updated_at,country_code,short_description,full_name,avatar_file_map,extra",
				"user_list_10"                       => "user_id,npc_type,invited_by_partner_id,invited_by_user_id,last_active_day_start_at,created_at,updated_at,full_name_updated_at,country_code,short_description,full_name,avatar_file_map,extra",
				"user_last_call_1"                   => "user_id,company_id,call_key,is_finished,type,created_at,updated_at,extra",
				"user_last_call_2"                   => "user_id,company_id,call_key,is_finished,type,created_at,updated_at,extra",
				"user_last_call_3"                   => "user_id,company_id,call_key,is_finished,type,created_at,updated_at,extra",
				"user_last_call_4"                   => "user_id,company_id,call_key,is_finished,type,created_at,updated_at,extra",
				"user_last_call_5"                   => "user_id,company_id,call_key,is_finished,type,created_at,updated_at,extra",
				"user_last_call_6"                   => "user_id,company_id,call_key,is_finished,type,created_at,updated_at,extra",
				"user_last_call_7"                   => "user_id,company_id,call_key,is_finished,type,created_at,updated_at,extra",
				"user_last_call_8"                   => "user_id,company_id,call_key,is_finished,type,created_at,updated_at,extra",
				"user_last_call_9"                   => "user_id,company_id,call_key,is_finished,type,created_at,updated_at,extra",
				"user_last_call_10"                  => "user_id,company_id,call_key,is_finished,type,created_at,updated_at,extra",
				"user_security_1"                    => "user_id,phone_number,created_at,updated_at",
				"user_security_2"                    => "user_id,phone_number,created_at,updated_at",
				"user_security_3"                    => "user_id,phone_number,created_at,updated_at",
				"user_security_4"                    => "user_id,phone_number,created_at,updated_at",
				"user_security_5"                    => "user_id,phone_number,created_at,updated_at",
				"user_security_6"                    => "user_id,phone_number,created_at,updated_at",
				"user_security_7"                    => "user_id,phone_number,created_at,updated_at",
				"user_security_8"                    => "user_id,phone_number,created_at,updated_at",
				"user_security_9"                    => "user_id,phone_number,created_at,updated_at",
				"user_security_10"                   => "user_id,phone_number,created_at,updated_at",
				"user_company_session_token_list_1"  => "user_company_session_token,user_id,session_uniq,status,company_id,created_at,updated_at",
				"user_company_session_token_list_2"  => "user_company_session_token,user_id,session_uniq,status,company_id,created_at,updated_at",
				"user_company_session_token_list_3"  => "user_company_session_token,user_id,session_uniq,status,company_id,created_at,updated_at",
				"user_company_session_token_list_4"  => "user_company_session_token,user_id,session_uniq,status,company_id,created_at,updated_at",
				"user_company_session_token_list_5"  => "user_company_session_token,user_id,session_uniq,status,company_id,created_at,updated_at",
				"user_company_session_token_list_6"  => "user_company_session_token,user_id,session_uniq,status,company_id,created_at,updated_at",
				"user_company_session_token_list_7"  => "user_company_session_token,user_id,session_uniq,status,company_id,created_at,updated_at",
				"user_company_session_token_list_8"  => "user_company_session_token,user_id,session_uniq,status,company_id,created_at,updated_at",
				"user_company_session_token_list_9"  => "user_company_session_token,user_id,session_uniq,status,company_id,created_at,updated_at",
				"user_company_session_token_list_10" => "user_company_session_token,user_id,session_uniq,status,company_id,created_at,updated_at",
				"company_lobby_list_1"               => "user_id,company_id,order,status,entry_id,created_at,updated_at,extra",
				"company_lobby_list_2"               => "user_id,company_id,order,status,entry_id,created_at,updated_at,extra",
				"company_lobby_list_3"               => "user_id,company_id,order,status,entry_id,created_at,updated_at,extra",
				"company_lobby_list_4"               => "user_id,company_id,order,status,entry_id,created_at,updated_at,extra",
				"company_lobby_list_5"               => "user_id,company_id,order,status,entry_id,created_at,updated_at,extra",
				"company_lobby_list_6"               => "user_id,company_id,order,status,entry_id,created_at,updated_at,extra",
				"company_lobby_list_7"               => "user_id,company_id,order,status,entry_id,created_at,updated_at,extra",
				"company_lobby_list_8"               => "user_id,company_id,order,status,entry_id,created_at,updated_at,extra",
				"company_lobby_list_9"               => "user_id,company_id,order,status,entry_id,created_at,updated_at,extra",
				"company_lobby_list_10"              => "user_id,company_id,order,status,entry_id,created_at,updated_at,extra",
				"company_list_1"                     => "user_id,company_id,is_has_pin,order,entry_id,created_at,updated_at,extra",
				"company_list_2"                     => "user_id,company_id,is_has_pin,order,entry_id,created_at,updated_at,extra",
				"company_list_3"                     => "user_id,company_id,is_has_pin,order,entry_id,created_at,updated_at,extra",
				"company_list_4"                     => "user_id,company_id,is_has_pin,order,entry_id,created_at,updated_at,extra",
				"company_list_5"                     => "user_id,company_id,is_has_pin,order,entry_id,created_at,updated_at,extra",
				"company_list_6"                     => "user_id,company_id,is_has_pin,order,entry_id,created_at,updated_at,extra",
				"company_list_7"                     => "user_id,company_id,is_has_pin,order,entry_id,created_at,updated_at,extra",
				"company_list_8"                     => "user_id,company_id,is_has_pin,order,entry_id,created_at,updated_at,extra",
				"company_list_9"                     => "user_id,company_id,is_has_pin,order,entry_id,created_at,updated_at,extra",
				"company_list_10"                    => "user_id,company_id,is_has_pin,order,entry_id,created_at,updated_at,extra",
				"company_inbox_1"                    => "user_id,company_id,messages_unread_count_alias,inbox_unread_count,created_at,updated_at",
				"company_inbox_2"                    => "user_id,company_id,messages_unread_count_alias,inbox_unread_count,created_at,updated_at",
				"company_inbox_3"                    => "user_id,company_id,messages_unread_count_alias,inbox_unread_count,created_at,updated_at",
				"company_inbox_4"                    => "user_id,company_id,messages_unread_count_alias,inbox_unread_count,created_at,updated_at",
				"company_inbox_5"                    => "user_id,company_id,messages_unread_count_alias,inbox_unread_count,created_at,updated_at",
				"company_inbox_6"                    => "user_id,company_id,messages_unread_count_alias,inbox_unread_count,created_at,updated_at",
				"company_inbox_7"                    => "user_id,company_id,messages_unread_count_alias,inbox_unread_count,created_at,updated_at",
				"company_inbox_8"                    => "user_id,company_id,messages_unread_count_alias,inbox_unread_count,created_at,updated_at",
				"company_inbox_9"                    => "user_id,company_id,messages_unread_count_alias,inbox_unread_count,created_at,updated_at",
				"company_inbox_10"                   => "user_id,company_id,messages_unread_count_alias,inbox_unread_count,created_at,updated_at",
				"session_active_list_1"              => "session_uniq,user_id,created_at,updated_at,login_at,refreshed_at,ua_hash,ip_address,extra",
				"session_active_list_2"              => "session_uniq,user_id,created_at,updated_at,login_at,refreshed_at,ua_hash,ip_address,extra",
				"session_active_list_3"              => "session_uniq,user_id,created_at,updated_at,login_at,refreshed_at,ua_hash,ip_address,extra",
				"session_active_list_4"              => "session_uniq,user_id,created_at,updated_at,login_at,refreshed_at,ua_hash,ip_address,extra",
				"session_active_list_5"              => "session_uniq,user_id,created_at,updated_at,login_at,refreshed_at,ua_hash,ip_address,extra",
				"session_active_list_6"              => "session_uniq,user_id,created_at,updated_at,login_at,refreshed_at,ua_hash,ip_address,extra",
				"session_active_list_7"              => "session_uniq,user_id,created_at,updated_at,login_at,refreshed_at,ua_hash,ip_address,extra",
				"session_active_list_8"              => "session_uniq,user_id,created_at,updated_at,login_at,refreshed_at,ua_hash,ip_address,extra",
				"session_active_list_9"              => "session_uniq,user_id,created_at,updated_at,login_at,refreshed_at,ua_hash,ip_address,extra",
				"session_active_list_10"             => "session_uniq,user_id,created_at,updated_at,login_at,refreshed_at,ua_hash,ip_address,extra",
				"notification_list_1"                => "user_id,snoozed_until,created_at,updated_at,device_list,extra",
				"notification_list_2"                => "user_id,snoozed_until,created_at,updated_at,device_list,extra",
				"notification_list_3"                => "user_id,snoozed_until,created_at,updated_at,device_list,extra",
				"notification_list_4"                => "user_id,snoozed_until,created_at,updated_at,device_list,extra",
				"notification_list_5"                => "user_id,snoozed_until,created_at,updated_at,device_list,extra",
				"notification_list_6"                => "user_id,snoozed_until,created_at,updated_at,device_list,extra",
				"notification_list_7"                => "user_id,snoozed_until,created_at,updated_at,device_list,extra",
				"notification_list_8"                => "user_id,snoozed_until,created_at,updated_at,device_list,extra",
				"notification_list_9"                => "user_id,snoozed_until,created_at,updated_at,device_list,extra",
				"notification_list_10"               => "user_id,snoozed_until,created_at,updated_at,device_list,extra",
				"notification_company_push_token_1"  => "token_hash,user_id,company_id,created_at,updated_at",
				"notification_company_push_token_2"  => "token_hash,user_id,company_id,created_at,updated_at",
				"notification_company_push_token_3"  => "token_hash,user_id,company_id,created_at,updated_at",
				"notification_company_push_token_4"  => "token_hash,user_id,company_id,created_at,updated_at",
				"notification_company_push_token_5"  => "token_hash,user_id,company_id,created_at,updated_at",
				"notification_company_push_token_6"  => "token_hash,user_id,company_id,created_at,updated_at",
				"notification_company_push_token_7"  => "token_hash,user_id,company_id,created_at,updated_at",
				"notification_company_push_token_8"  => "token_hash,user_id,company_id,created_at,updated_at",
				"notification_company_push_token_9"  => "token_hash,user_id,company_id,created_at,updated_at",
				"notification_company_push_token_10" => "token_hash,user_id,company_id,created_at,updated_at",
				"mbti_selection_list"                => "user_id,mbti_type,text_type,created_at,updated_at,color_selection_list",
				"premium_status_1"                   => "user_id,need_block_if_inactive,free_active_till,active_till,created_at,updated_at,last_prolongation_at,last_prolongation_duration,last_prolongation_user_id,last_prolongation_payment_id,extra",
				"premium_status_2"                   => "user_id,need_block_if_inactive,free_active_till,active_till,created_at,updated_at,last_prolongation_at,last_prolongation_duration,last_prolongation_user_id,last_prolongation_payment_id,extra",
				"premium_status_3"                   => "user_id,need_block_if_inactive,free_active_till,active_till,created_at,updated_at,last_prolongation_at,last_prolongation_duration,last_prolongation_user_id,last_prolongation_payment_id,extra",
				"premium_status_4"                   => "user_id,need_block_if_inactive,free_active_till,active_till,created_at,updated_at,last_prolongation_at,last_prolongation_duration,last_prolongation_user_id,last_prolongation_payment_id,extra",
				"premium_status_5"                   => "user_id,need_block_if_inactive,free_active_till,active_till,created_at,updated_at,last_prolongation_at,last_prolongation_duration,last_prolongation_user_id,last_prolongation_payment_id,extra",
				"premium_status_6"                   => "user_id,need_block_if_inactive,free_active_till,active_till,created_at,updated_at,last_prolongation_at,last_prolongation_duration,last_prolongation_user_id,last_prolongation_payment_id,extra",
				"premium_status_7"                   => "user_id,need_block_if_inactive,free_active_till,active_till,created_at,updated_at,last_prolongation_at,last_prolongation_duration,last_prolongation_user_id,last_prolongation_payment_id,extra",
				"premium_status_8"                   => "user_id,need_block_if_inactive,free_active_till,active_till,created_at,updated_at,last_prolongation_at,last_prolongation_duration,last_prolongation_user_id,last_prolongation_payment_id,extra",
				"premium_status_9"                   => "user_id,need_block_if_inactive,free_active_till,active_till,created_at,updated_at,last_prolongation_at,last_prolongation_duration,last_prolongation_user_id,last_prolongation_payment_id,extra",
				"premium_status_10"                  => "user_id,need_block_if_inactive,free_active_till,active_till,created_at,updated_at,last_prolongation_at,last_prolongation_duration,last_prolongation_user_id,last_prolongation_payment_id,extra",
				"premium_prolongation_history_1"     => "id,user_id,action,created_at,duration,active_till,doer_user_id,payment_id,extra",
				"premium_prolongation_history_2"     => "id,user_id,action,created_at,duration,active_till,doer_user_id,payment_id,extra",
				"premium_prolongation_history_3"     => "id,user_id,action,created_at,duration,active_till,doer_user_id,payment_id,extra",
				"premium_prolongation_history_4"     => "id,user_id,action,created_at,duration,active_till,doer_user_id,payment_id,extra",
				"premium_prolongation_history_5"     => "id,user_id,action,created_at,duration,active_till,doer_user_id,payment_id,extra",
				"premium_prolongation_history_6"     => "id,user_id,action,created_at,duration,active_till,doer_user_id,payment_id,extra",
				"premium_prolongation_history_7"     => "id,user_id,action,created_at,duration,active_till,doer_user_id,payment_id,extra",
				"premium_prolongation_history_8"     => "id,user_id,action,created_at,duration,active_till,doer_user_id,payment_id,extra",
				"premium_prolongation_history_9"     => "id,user_id,action,created_at,duration,active_till,doer_user_id,payment_id,extra",
				"premium_prolongation_history_10"    => "id,user_id,action,created_at,duration,active_till,doer_user_id,payment_id,extra",
				"used_premium_promo_product_1"       => "user_id,label,created_at",
				"used_premium_promo_product_2"       => "user_id,label,created_at",
				"used_premium_promo_product_3"       => "user_id,label,created_at",
				"used_premium_promo_product_4"       => "user_id,label,created_at",
				"used_premium_promo_product_5"       => "user_id,label,created_at",
				"used_premium_promo_product_6"       => "user_id,label,created_at",
				"used_premium_promo_product_7"       => "user_id,label,created_at",
				"used_premium_promo_product_8"       => "user_id,label,created_at",
				"used_premium_promo_product_9"       => "user_id,label,created_at",
				"used_premium_promo_product_10"      => "user_id,label,created_at",
				"denied_user_free_premium"           => "user_id,created_at,reason_type",
				"space_payment_history_1"            => "id,user_id,space_id,tariff_plan_id,payment_id,payment_at,created_at,updated_at",
				"space_payment_history_2"            => "id,user_id,space_id,tariff_plan_id,payment_id,payment_at,created_at,updated_at",
				"space_payment_history_3"            => "id,user_id,space_id,tariff_plan_id,payment_id,payment_at,created_at,updated_at",
				"space_payment_history_4"            => "id,user_id,space_id,tariff_plan_id,payment_id,payment_at,created_at,updated_at",
				"space_payment_history_5"            => "id,user_id,space_id,tariff_plan_id,payment_id,payment_at,created_at,updated_at",
				"space_payment_history_6"            => "id,user_id,space_id,tariff_plan_id,payment_id,payment_at,created_at,updated_at",
				"space_payment_history_7"            => "id,user_id,space_id,tariff_plan_id,payment_id,payment_at,created_at,updated_at",
				"space_payment_history_8"            => "id,user_id,space_id,tariff_plan_id,payment_id,payment_at,created_at,updated_at",
				"space_payment_history_9"            => "id,user_id,space_id,tariff_plan_id,payment_id,payment_at,created_at,updated_at",
				"space_payment_history_10"           => "id,user_id,space_id,tariff_plan_id,payment_id,payment_at,created_at,updated_at",
			],
		],
	],

	##########################################################
	# endregion
	##########################################################

	##########################################################
	# region pivot_user_{20m} - база данных, хранящая пользователей
	##########################################################

	// база данных, хранящая пользователей
	"pivot_user_20m"    => [
		"db"      => "pivot_user_20m",
		"mysql"   => [
			"host" => MYSQL_PIVOT_USER_HOST,
			"user" => MYSQL_PIVOT_USER_USER,
			"pass" => MYSQL_PIVOT_USER_PASS,
			"ssl"  => MYSQL_PIVOT_USER_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"user_list_11"                       => "user_id,npc_type,invited_by_partner_id,invited_by_user_id,last_active_day_start_at,created_at,updated_at,full_name_updated_at,country_code,short_description,full_name,avatar_file_map,extra",
				"user_list_12"                       => "user_id,npc_type,invited_by_partner_id,invited_by_user_id,last_active_day_start_at,created_at,updated_at,full_name_updated_at,country_code,short_description,full_name,avatar_file_map,extra",
				"user_list_13"                       => "user_id,npc_type,invited_by_partner_id,invited_by_user_id,last_active_day_start_at,created_at,updated_at,full_name_updated_at,country_code,short_description,full_name,avatar_file_map,extra",
				"user_list_14"                       => "user_id,npc_type,invited_by_partner_id,invited_by_user_id,last_active_day_start_at,created_at,updated_at,full_name_updated_at,country_code,short_description,full_name,avatar_file_map,extra",
				"user_list_15"                       => "user_id,npc_type,invited_by_partner_id,invited_by_user_id,last_active_day_start_at,created_at,updated_at,full_name_updated_at,country_code,short_description,full_name,avatar_file_map,extra",
				"user_list_16"                       => "user_id,npc_type,invited_by_partner_id,invited_by_user_id,last_active_day_start_at,created_at,updated_at,full_name_updated_at,country_code,short_description,full_name,avatar_file_map,extra",
				"user_list_17"                       => "user_id,npc_type,invited_by_partner_id,invited_by_user_id,last_active_day_start_at,created_at,updated_at,full_name_updated_at,country_code,short_description,full_name,avatar_file_map,extra",
				"user_list_18"                       => "user_id,npc_type,invited_by_partner_id,invited_by_user_id,last_active_day_start_at,created_at,updated_at,full_name_updated_at,country_code,short_description,full_name,avatar_file_map,extra",
				"user_list_19"                       => "user_id,npc_type,invited_by_partner_id,invited_by_user_id,last_active_day_start_at,created_at,updated_at,full_name_updated_at,country_code,short_description,full_name,avatar_file_map,extra",
				"user_list_20"                       => "user_id,npc_type,invited_by_partner_id,invited_by_user_id,last_active_day_start_at,created_at,updated_at,full_name_updated_at,country_code,short_description,full_name,avatar_file_map,extra",
				"user_last_call_11"                  => "user_id,company_id,call_key,is_finished,type,created_at,updated_at,extra",
				"user_last_call_12"                  => "user_id,company_id,call_key,is_finished,type,created_at,updated_at,extra",
				"user_last_call_13"                  => "user_id,company_id,call_key,is_finished,type,created_at,updated_at,extra",
				"user_last_call_14"                  => "user_id,company_id,call_key,is_finished,type,created_at,updated_at,extra",
				"user_last_call_15"                  => "user_id,company_id,call_key,is_finished,type,created_at,updated_at,extra",
				"user_last_call_16"                  => "user_id,company_id,call_key,is_finished,type,created_at,updated_at,extra",
				"user_last_call_17"                  => "user_id,company_id,call_key,is_finished,type,created_at,updated_at,extra",
				"user_last_call_18"                  => "user_id,company_id,call_key,is_finished,type,created_at,updated_at,extra",
				"user_last_call_19"                  => "user_id,company_id,call_key,is_finished,type,created_at,updated_at,extra",
				"user_last_call_20"                  => "user_id,company_id,call_key,is_finished,type,created_at,updated_at,extra",
				"user_security_11"                   => "user_id,phone_number,created_at,updated_at",
				"user_security_12"                   => "user_id,phone_number,created_at,updated_at",
				"user_security_13"                   => "user_id,phone_number,created_at,updated_at",
				"user_security_14"                   => "user_id,phone_number,created_at,updated_at",
				"user_security_15"                   => "user_id,phone_number,created_at,updated_at",
				"user_security_16"                   => "user_id,phone_number,created_at,updated_at",
				"user_security_17"                   => "user_id,phone_number,created_at,updated_at",
				"user_security_18"                   => "user_id,phone_number,created_at,updated_at",
				"user_security_19"                   => "user_id,phone_number,created_at,updated_at",
				"user_security_20"                   => "user_id,phone_number,created_at,updated_at",
				"user_company_session_token_list_11" => "user_company_session_token,user_id,session_uniq,status,company_id,created_at,updated_at",
				"user_company_session_token_list_12" => "user_company_session_token,user_id,session_uniq,status,company_id,created_at,updated_at",
				"user_company_session_token_list_13" => "user_company_session_token,user_id,session_uniq,status,company_id,created_at,updated_at",
				"user_company_session_token_list_14" => "user_company_session_token,user_id,session_uniq,status,company_id,created_at,updated_at",
				"user_company_session_token_list_15" => "user_company_session_token,user_id,session_uniq,status,company_id,created_at,updated_at",
				"user_company_session_token_list_16" => "user_company_session_token,user_id,session_uniq,status,company_id,created_at,updated_at",
				"user_company_session_token_list_17" => "user_company_session_token,user_id,session_uniq,status,company_id,created_at,updated_at",
				"user_company_session_token_list_18" => "user_company_session_token,user_id,session_uniq,status,company_id,created_at,updated_at",
				"user_company_session_token_list_19" => "user_company_session_token,user_id,session_uniq,status,company_id,created_at,updated_at",
				"user_company_session_token_list_20" => "user_company_session_token,user_id,session_uniq,status,company_id,created_at,updated_at",
				"company_lobby_list_11"              => "user_id,company_id,order,status,entry_id,created_at,updated_at,extra",
				"company_lobby_list_12"              => "user_id,company_id,order,status,entry_id,created_at,updated_at,extra",
				"company_lobby_list_13"              => "user_id,company_id,order,status,entry_id,created_at,updated_at,extra",
				"company_lobby_list_14"              => "user_id,company_id,order,status,entry_id,created_at,updated_at,extra",
				"company_lobby_list_15"              => "user_id,company_id,order,status,entry_id,created_at,updated_at,extra",
				"company_lobby_list_16"              => "user_id,company_id,order,status,entry_id,created_at,updated_at,extra",
				"company_lobby_list_17"              => "user_id,company_id,order,status,entry_id,created_at,updated_at,extra",
				"company_lobby_list_18"              => "user_id,company_id,order,status,entry_id,created_at,updated_at,extra",
				"company_lobby_list_19"              => "user_id,company_id,order,status,entry_id,created_at,updated_at,extra",
				"company_lobby_list_20"              => "user_id,company_id,order,status,entry_id,created_at,updated_at,extra",
				"company_list_11"                    => "user_id,company_id,is_has_pin,order,entry_id,created_at,updated_at,extra",
				"company_list_12"                    => "user_id,company_id,is_has_pin,order,entry_id,created_at,updated_at,extra",
				"company_list_13"                    => "user_id,company_id,is_has_pin,order,entry_id,created_at,updated_at,extra",
				"company_list_14"                    => "user_id,company_id,is_has_pin,order,entry_id,created_at,updated_at,extra",
				"company_list_15"                    => "user_id,company_id,is_has_pin,order,entry_id,created_at,updated_at,extra",
				"company_list_16"                    => "user_id,company_id,is_has_pin,order,entry_id,created_at,updated_at,extra",
				"company_list_17"                    => "user_id,company_id,is_has_pin,order,entry_id,created_at,updated_at,extra",
				"company_list_18"                    => "user_id,company_id,is_has_pin,order,entry_id,created_at,updated_at,extra",
				"company_list_19"                    => "user_id,company_id,is_has_pin,order,entry_id,created_at,updated_at,extra",
				"company_list_20"                    => "user_id,company_id,is_has_pin,order,entry_id,created_at,updated_at,extra",
				"company_inbox_11"                   => "user_id,company_id,messages_unread_count_alias,inbox_unread_count,created_at,updated_at",
				"company_inbox_12"                   => "user_id,company_id,messages_unread_count_alias,inbox_unread_count,created_at,updated_at",
				"company_inbox_13"                   => "user_id,company_id,messages_unread_count_alias,inbox_unread_count,created_at,updated_at",
				"company_inbox_14"                   => "user_id,company_id,messages_unread_count_alias,inbox_unread_count,created_at,updated_at",
				"company_inbox_15"                   => "user_id,company_id,messages_unread_count_alias,inbox_unread_count,created_at,updated_at",
				"company_inbox_16"                   => "user_id,company_id,messages_unread_count_alias,inbox_unread_count,created_at,updated_at",
				"company_inbox_17"                   => "user_id,company_id,messages_unread_count_alias,inbox_unread_count,created_at,updated_at",
				"company_inbox_18"                   => "user_id,company_id,messages_unread_count_alias,inbox_unread_count,created_at,updated_at",
				"company_inbox_19"                   => "user_id,company_id,messages_unread_count_alias,inbox_unread_count,created_at,updated_at",
				"company_inbox_20"                   => "user_id,company_id,messages_unread_count_alias,inbox_unread_count,created_at,updated_at",
				"session_active_list_11"             => "session_uniq,user_id,created_at,updated_at,login_at,refreshed_at,ua_hash,ip_address,extra",
				"session_active_list_12"             => "session_uniq,user_id,created_at,updated_at,login_at,refreshed_at,ua_hash,ip_address,extra",
				"session_active_list_13"             => "session_uniq,user_id,created_at,updated_at,login_at,refreshed_at,ua_hash,ip_address,extra",
				"session_active_list_14"             => "session_uniq,user_id,created_at,updated_at,login_at,refreshed_at,ua_hash,ip_address,extra",
				"session_active_list_15"             => "session_uniq,user_id,created_at,updated_at,login_at,refreshed_at,ua_hash,ip_address,extra",
				"session_active_list_16"             => "session_uniq,user_id,created_at,updated_at,login_at,refreshed_at,ua_hash,ip_address,extra",
				"session_active_list_17"             => "session_uniq,user_id,created_at,updated_at,login_at,refreshed_at,ua_hash,ip_address,extra",
				"session_active_list_18"             => "session_uniq,user_id,created_at,updated_at,login_at,refreshed_at,ua_hash,ip_address,extra",
				"session_active_list_19"             => "session_uniq,user_id,created_at,updated_at,login_at,refreshed_at,ua_hash,ip_address,extra",
				"session_active_list_20"             => "session_uniq,user_id,created_at,updated_at,login_at,refreshed_at,ua_hash,ip_address,extra",
				"notification_list_11"               => "user_id,snoozed_until,created_at,updated_at,device_list,extra",
				"notification_list_12"               => "user_id,snoozed_until,created_at,updated_at,device_list,extra",
				"notification_list_13"               => "user_id,snoozed_until,created_at,updated_at,device_list,extra",
				"notification_list_14"               => "user_id,snoozed_until,created_at,updated_at,device_list,extra",
				"notification_list_15"               => "user_id,snoozed_until,created_at,updated_at,device_list,extra",
				"notification_list_16"               => "user_id,snoozed_until,created_at,updated_at,device_list,extra",
				"notification_list_17"               => "user_id,snoozed_until,created_at,updated_at,device_list,extra",
				"notification_list_18"               => "user_id,snoozed_until,created_at,updated_at,device_list,extra",
				"notification_list_19"               => "user_id,snoozed_until,created_at,updated_at,device_list,extra",
				"notification_list_20"               => "user_id,snoozed_until,created_at,updated_at,device_list,extra",
				"notification_company_push_token_11" => "token_hash,user_id,company_id,created_at,updated_at",
				"notification_company_push_token_12" => "token_hash,user_id,company_id,created_at,updated_at",
				"notification_company_push_token_13" => "token_hash,user_id,company_id,created_at,updated_at",
				"notification_company_push_token_14" => "token_hash,user_id,company_id,created_at,updated_at",
				"notification_company_push_token_15" => "token_hash,user_id,company_id,created_at,updated_at",
				"notification_company_push_token_16" => "token_hash,user_id,company_id,created_at,updated_at",
				"notification_company_push_token_17" => "token_hash,user_id,company_id,created_at,updated_at",
				"notification_company_push_token_18" => "token_hash,user_id,company_id,created_at,updated_at",
				"notification_company_push_token_19" => "token_hash,user_id,company_id,created_at,updated_at",
				"notification_company_push_token_20" => "token_hash,user_id,company_id,created_at,updated_at",
				"mbti_selection_list"                => "user_id,mbti_type,text_type,created_at,updated_at,color_selection_list",
				"premium_status_11"                  => "user_id,need_block_if_inactive,free_active_till,active_till,created_at,updated_at,last_prolongation_at,last_prolongation_duration,last_prolongation_user_id,last_prolongation_payment_id,extra",
				"premium_status_12"                  => "user_id,need_block_if_inactive,free_active_till,active_till,created_at,updated_at,last_prolongation_at,last_prolongation_duration,last_prolongation_user_id,last_prolongation_payment_id,extra",
				"premium_status_13"                  => "user_id,need_block_if_inactive,free_active_till,active_till,created_at,updated_at,last_prolongation_at,last_prolongation_duration,last_prolongation_user_id,last_prolongation_payment_id,extra",
				"premium_status_14"                  => "user_id,need_block_if_inactive,free_active_till,active_till,created_at,updated_at,last_prolongation_at,last_prolongation_duration,last_prolongation_user_id,last_prolongation_payment_id,extra",
				"premium_status_15"                  => "user_id,need_block_if_inactive,free_active_till,active_till,created_at,updated_at,last_prolongation_at,last_prolongation_duration,last_prolongation_user_id,last_prolongation_payment_id,extra",
				"premium_status_16"                  => "user_id,need_block_if_inactive,free_active_till,active_till,created_at,updated_at,last_prolongation_at,last_prolongation_duration,last_prolongation_user_id,last_prolongation_payment_id,extra",
				"premium_status_17"                  => "user_id,need_block_if_inactive,free_active_till,active_till,created_at,updated_at,last_prolongation_at,last_prolongation_duration,last_prolongation_user_id,last_prolongation_payment_id,extra",
				"premium_status_18"                  => "user_id,need_block_if_inactive,free_active_till,active_till,created_at,updated_at,last_prolongation_at,last_prolongation_duration,last_prolongation_user_id,last_prolongation_payment_id,extra",
				"premium_status_19"                  => "user_id,need_block_if_inactive,free_active_till,active_till,created_at,updated_at,last_prolongation_at,last_prolongation_duration,last_prolongation_user_id,last_prolongation_payment_id,extra",
				"premium_status_20"                  => "user_id,need_block_if_inactive,free_active_till,active_till,created_at,updated_at,last_prolongation_at,last_prolongation_duration,last_prolongation_user_id,last_prolongation_payment_id,extra",
				"premium_prolongation_history_11"    => "id,user_id,action,created_at,duration,active_till,doer_user_id,payment_id,extra",
				"premium_prolongation_history_12"    => "id,user_id,action,created_at,duration,active_till,doer_user_id,payment_id,extra",
				"premium_prolongation_history_13"    => "id,user_id,action,created_at,duration,active_till,doer_user_id,payment_id,extra",
				"premium_prolongation_history_14"    => "id,user_id,action,created_at,duration,active_till,doer_user_id,payment_id,extra",
				"premium_prolongation_history_15"    => "id,user_id,action,created_at,duration,active_till,doer_user_id,payment_id,extra",
				"premium_prolongation_history_16"    => "id,user_id,action,created_at,duration,active_till,doer_user_id,payment_id,extra",
				"premium_prolongation_history_17"    => "id,user_id,action,created_at,duration,active_till,doer_user_id,payment_id,extra",
				"premium_prolongation_history_18"    => "id,user_id,action,created_at,duration,active_till,doer_user_id,payment_id,extra",
				"premium_prolongation_history_19"    => "id,user_id,action,created_at,duration,active_till,doer_user_id,payment_id,extra",
				"premium_prolongation_history_20"    => "id,user_id,action,created_at,duration,active_till,doer_user_id,payment_id,extra",
				"used_premium_promo_product_11"      => "user_id,label,created_at",
				"used_premium_promo_product_12"      => "user_id,label,created_at",
				"used_premium_promo_product_13"      => "user_id,label,created_at",
				"used_premium_promo_product_14"      => "user_id,label,created_at",
				"used_premium_promo_product_15"      => "user_id,label,created_at",
				"used_premium_promo_product_16"      => "user_id,label,created_at",
				"used_premium_promo_product_17"      => "user_id,label,created_at",
				"used_premium_promo_product_18"      => "user_id,label,created_at",
				"used_premium_promo_product_19"      => "user_id,label,created_at",
				"used_premium_promo_product_20"      => "user_id,label,created_at",
				"denied_user_free_premium"           => "user_id,created_at,reason_type",
				"space_payment_history_11"           => "id,user_id,space_id,tariff_plan_id,payment_id,payment_at,created_at,updated_at",
				"space_payment_history_12"           => "id,user_id,space_id,tariff_plan_id,payment_id,payment_at,created_at,updated_at",
				"space_payment_history_13"           => "id,user_id,space_id,tariff_plan_id,payment_id,payment_at,created_at,updated_at",
				"space_payment_history_14"           => "id,user_id,space_id,tariff_plan_id,payment_id,payment_at,created_at,updated_at",
				"space_payment_history_15"           => "id,user_id,space_id,tariff_plan_id,payment_id,payment_at,created_at,updated_at",
				"space_payment_history_16"           => "id,user_id,space_id,tariff_plan_id,payment_id,payment_at,created_at,updated_at",
				"space_payment_history_17"           => "id,user_id,space_id,tariff_plan_id,payment_id,payment_at,created_at,updated_at",
				"space_payment_history_18"           => "id,user_id,space_id,tariff_plan_id,payment_id,payment_at,created_at,updated_at",
				"space_payment_history_19"           => "id,user_id,space_id,tariff_plan_id,payment_id,payment_at,created_at,updated_at",
				"space_payment_history_20"           => "id,user_id,space_id,tariff_plan_id,payment_id,payment_at,created_at,updated_at",
			],
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
			"host" => MYSQL_PIVOT_SYSTEM_HOST,
			"user" => MYSQL_PIVOT_SYSTEM_USER,
			"pass" => MYSQL_PIVOT_SYSTEM_PASS,
			"ssl"  => MYSQL_PIVOT_SYSTEM_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"antispam_company"  => "company_id,key,is_stat_sent,count,expires_at",
				"antispam_ip"       => "ip_address,key,is_stat_sent,count,expires_at",
				"antispam_phone"    => "phone_number_hash,key,is_stat_sent,count,expires_at",
				"antispam_user"     => "user_id,key,is_stat_sent,count,expires_at",
				"auto_increment"    => "key,value",
				"datastore"         => "key,extra",
				"phphooker_queue"   => "task_id,task_type,need_work,error_count,created_at,task_global_key,params",
				"default_file_list" => "dictionary_key,file_key,file_hash,extra",
				"unit_test"         => "key,int_row,extra",
			],
		],
	],

	"pivot_company_service" => [
		"db"      => "pivot_company_service",
		"mysql"   => [
			"host" => MYSQL_PIVOT_USER_HOST,
			"user" => MYSQL_PIVOT_USER_USER,
			"pass" => MYSQL_PIVOT_USER_PASS,
			"ssl"  => MYSQL_PIVOT_USER_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"company_registry"             => "company_id,is_busy,is_hibernated,is_mysql_alive,created_at,updated_at",
				"company_service_task"         => "task_id,is_failed,need_work,type,started_at,finished_at,created_at,updated_at,company_id,logs,data",
				"company_service_task_history" => "task_id,is_failed,need_work,type,started_at,finished_at,created_at,updated_at,company_id,logs,data",
				"domino_registry"              => "domino_id,code_host,database_host,is_company_creating_allowed,hibernation_locked_until,tier,common_port_count,service_port_count,reserved_port_count,common_active_port_count,reserve_active_port_count,service_active_port_count,created_at,updated_at,extra",
				"company_init_registry"        => "company_id,is_vacant,is_deleted,is_purged,creating_started_at,creating_finished_at,became_vacant_at,occupation_started_at,occupation_finished_at,deleted_at,purged_at,created_at,updated_at,occupant_user_id,deleter_user_id,logs,extra",
			],
		],
	],
	##########################################################
	# endregion
	##########################################################

	##########################################################
	# region pivot_auth_{Y} - база данных, хранящая лог авторизации
	##########################################################

	"pivot_auth_2021" => [
		"db"      => "pivot_auth_2021",
		"mysql"   => [
			"host" => MYSQL_PIVOT_AUTH_HOST,
			"user" => MYSQL_PIVOT_AUTH_USER,
			"pass" => MYSQL_PIVOT_AUTH_PASS,
			"ssl"  => MYSQL_PIVOT_AUTH_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_INT,
				"data" => \shardingConf::makeDataForIntShardingType(1, 12),
			],
			"tables"        => [
				"auth_list"       => "auth_uniq,user_id,is_success,type,created_at,updated_at,expires_at,ua_hash,ip_address",
				"auth_phone_list" => "auth_map,is_success,resend_count,error_count,created_at,updated_at,next_resend_at,sms_id,sms_code_hash,phone_number",
				"2fa_list"        => "2fa_map,user_id,company_id,is_active,is_success,action_type,created_at,updated_at,expires_at",
				"2fa_phone_list"  => "2fa_map,is_success,resend_count,error_count,created_at,updated_at,next_resend_at,sms_id,sms_code_hash,phone_number",
			],
		],
	],

	"pivot_auth_2022" => [
		"db"      => "pivot_auth_2022",
		"mysql"   => [
			"host" => MYSQL_PIVOT_AUTH_HOST,
			"user" => MYSQL_PIVOT_AUTH_USER,
			"pass" => MYSQL_PIVOT_AUTH_PASS,
			"ssl"  => MYSQL_PIVOT_AUTH_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_INT,
				"data" => \shardingConf::makeDataForIntShardingType(1, 12),
			],
			"tables"        => [
				"auth_list"       => "auth_uniq,user_id,is_success,type,created_at,updated_at,expires_at,ua_hash,ip_address",
				"auth_phone_list" => "auth_map,is_success,resend_count,error_count,created_at,updated_at,next_resend_at,sms_id,sms_code_hash,phone_number",
				"2fa_list"        => "2fa_map,user_id,company_id,is_active,is_success,action_type,created_at,updated_at,expires_at",
				"2fa_phone_list"  => "2fa_map,is_success,resend_count,error_count,created_at,updated_at,next_resend_at,sms_id,sms_code_hash,phone_number",
			],
		],
	],

	"pivot_auth_2023" => [
		"db"      => "pivot_auth_2023",
		"mysql"   => [
			"host" => MYSQL_PIVOT_AUTH_HOST,
			"user" => MYSQL_PIVOT_AUTH_USER,
			"pass" => MYSQL_PIVOT_AUTH_PASS,
			"ssl"  => MYSQL_PIVOT_AUTH_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_INT,
				"data" => \shardingConf::makeDataForIntShardingType(1, 12),
			],
			"tables"        => [
				"auth_list"       => "auth_uniq,user_id,is_success,type,created_at,updated_at,expires_at,ua_hash,ip_address",
				"auth_phone_list" => "auth_map,is_success,resend_count,error_count,created_at,updated_at,next_resend_at,sms_id,sms_code_hash,phone_number",
				"2fa_list"        => "2fa_map,user_id,company_id,is_active,is_success,action_type,created_at,updated_at,expires_at",
				"2fa_phone_list"  => "2fa_map,is_success,resend_count,error_count,created_at,updated_at,next_resend_at,sms_id,sms_code_hash,phone_number",
			],
		],
	],

	"pivot_auth_2024" => [
		"db"      => "pivot_auth_2024",
		"mysql"   => [
			"host" => MYSQL_PIVOT_AUTH_HOST,
			"user" => MYSQL_PIVOT_AUTH_USER,
			"pass" => MYSQL_PIVOT_AUTH_PASS,
			"ssl"  => MYSQL_PIVOT_AUTH_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_INT,
				"data" => \shardingConf::makeDataForIntShardingType(1, 12),
			],
			"tables"        => [
				"auth_list"       => "auth_uniq,user_id,is_success,type,created_at,updated_at,expires_at,ua_hash,ip_address",
				"auth_phone_list" => "auth_map,is_success,resend_count,error_count,created_at,updated_at,next_resend_at,sms_id,sms_code_hash,phone_number",
				"2fa_list"        => "2fa_map,user_id,company_id,is_active,is_success,action_type,created_at,updated_at,expires_at",
				"2fa_phone_list"  => "2fa_map,is_success,resend_count,error_count,created_at,updated_at,next_resend_at,sms_id,sms_code_hash,phone_number",
			],
		],
	],

	"pivot_auth_2025" => [
		"db"      => "pivot_auth_2025",
		"mysql"   => [
			"host" => MYSQL_PIVOT_AUTH_HOST,
			"user" => MYSQL_PIVOT_AUTH_USER,
			"pass" => MYSQL_PIVOT_AUTH_PASS,
			"ssl"  => MYSQL_PIVOT_AUTH_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_INT,
				"data" => \shardingConf::makeDataForIntShardingType(1, 12),
			],
			"tables"        => [
				"auth_list"       => "auth_uniq,user_id,is_success,type,created_at,updated_at,expires_at,ua_hash,ip_address",
				"auth_phone_list" => "auth_map,is_success,resend_count,error_count,created_at,updated_at,next_resend_at,sms_id,sms_code_hash,phone_number",
				"2fa_list"        => "2fa_map,user_id,company_id,is_active,is_success,action_type,created_at,updated_at,expires_at",
				"2fa_phone_list"  => "2fa_map,is_success,resend_count,error_count,created_at,updated_at,next_resend_at,sms_id,sms_code_hash,phone_number",
			],
		],
	],

	"pivot_auth_2026" => [
		"db"      => "pivot_auth_2026",
		"mysql"   => [
			"host" => MYSQL_PIVOT_AUTH_HOST,
			"user" => MYSQL_PIVOT_AUTH_USER,
			"pass" => MYSQL_PIVOT_AUTH_PASS,
			"ssl"  => MYSQL_PIVOT_AUTH_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_INT,
				"data" => \shardingConf::makeDataForIntShardingType(1, 12),
			],
			"tables"        => [
				"auth_list"       => "auth_uniq,user_id,is_success,type,created_at,updated_at,expires_at,ua_hash,ip_address",
				"auth_phone_list" => "auth_map,is_success,resend_count,error_count,created_at,updated_at,next_resend_at,sms_id,sms_code_hash,phone_number",
				"2fa_list"        => "2fa_map,user_id,company_id,is_active,is_success,action_type,created_at,updated_at,expires_at",
				"2fa_phone_list"  => "2fa_map,is_success,resend_count,error_count,created_at,updated_at,next_resend_at,sms_id,sms_code_hash,phone_number",
			],
		],
	],

	"pivot_auth_2027" => [
		"db"      => "pivot_auth_2027",
		"mysql"   => [
			"host" => MYSQL_PIVOT_AUTH_HOST,
			"user" => MYSQL_PIVOT_AUTH_USER,
			"pass" => MYSQL_PIVOT_AUTH_PASS,
			"ssl"  => MYSQL_PIVOT_AUTH_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_INT,
				"data" => \shardingConf::makeDataForIntShardingType(1, 12),
			],
			"tables"        => [
				"auth_list"       => "auth_uniq,user_id,is_success,type,created_at,updated_at,expires_at,ua_hash,ip_address",
				"auth_phone_list" => "auth_map,is_success,resend_count,error_count,created_at,updated_at,next_resend_at,sms_id,sms_code_hash,phone_number",
				"2fa_list"        => "2fa_map,user_id,company_id,is_active,is_success,action_type,created_at,updated_at,expires_at",
				"2fa_phone_list"  => "2fa_map,is_success,resend_count,error_count,created_at,updated_at,next_resend_at,sms_id,sms_code_hash,phone_number",
			],
		],
	],

	"pivot_auth_2028" => [
		"db"      => "pivot_auth_2028",
		"mysql"   => [
			"host" => MYSQL_PIVOT_AUTH_HOST,
			"user" => MYSQL_PIVOT_AUTH_USER,
			"pass" => MYSQL_PIVOT_AUTH_PASS,
			"ssl"  => MYSQL_PIVOT_AUTH_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_INT,
				"data" => \shardingConf::makeDataForIntShardingType(1, 12),
			],
			"tables"        => [
				"auth_list"       => "auth_uniq,user_id,is_success,type,created_at,updated_at,expires_at,ua_hash,ip_address",
				"auth_phone_list" => "auth_map,is_success,resend_count,error_count,created_at,updated_at,next_resend_at,sms_id,sms_code_hash,phone_number",
				"2fa_list"        => "2fa_map,user_id,company_id,is_active,is_success,action_type,created_at,updated_at,expires_at",
				"2fa_phone_list"  => "2fa_map,is_success,resend_count,error_count,created_at,updated_at,next_resend_at,sms_id,sms_code_hash,phone_number",
			],
		],
	],

	##########################################################
	# endregion
	##########################################################

	##########################################################
	# region pivot_history_logs_{Y} - база данных, хранящая в себе историю действий
	##########################################################

	"pivot_history_logs_2021" => [
		"db"      => "pivot_history_logs_2021",
		"mysql"   => [
			"host" => MYSQL_PIVOT_HISTORY_LOGS_HOST,
			"user" => MYSQL_PIVOT_HISTORY_LOGS_USER,
			"pass" => MYSQL_PIVOT_HISTORY_LOGS_PASS,
			"ssl"  => MYSQL_PIVOT_HISTORY_LOGS_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"user_auth_history"          => "auth_map,user_id,status,created_at,updated_at",
				"user_change_phone_history"  => "user_id,created_at,updated_at,previous_phone_number,new_phone_number,change_phone_story_map",
				"user_action_history"        => "row_id,user_id,type,created_at,extra",
				"session_history"            => "session_uniq,user_id,status,login_at,logout_at,ua_hash,ip_address,extra",
				"join_link_validate_history" => "history_id,join_link_uniq,user_id,session_uniq,input_link,created_at,extra",
				"join_link_accepted_history" => "history_id,join_link_uniq,user_id,company_id,entry_id,session_uniq,created_at,extra",
				"company_history"            => "log_id,company_id,type,created_at,extra",
				"send_history"               => "row_id,sms_id,is_success,task_created_at_ms,send_to_provider_at_ms,sms_sent_at_ms,created_at,provider_id,provider_response_code,provider_response,extra_alias",
			],
		],
	],

	"pivot_history_logs_2022" => [
		"db"      => "pivot_history_logs_2022",
		"mysql"   => [
			"host" => MYSQL_PIVOT_HISTORY_LOGS_HOST,
			"user" => MYSQL_PIVOT_HISTORY_LOGS_USER,
			"pass" => MYSQL_PIVOT_HISTORY_LOGS_PASS,
			"ssl"  => MYSQL_PIVOT_HISTORY_LOGS_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"user_auth_history"          => "auth_map,user_id,status,created_at,updated_at",
				"user_change_phone_history"  => "user_id,created_at,updated_at,previous_phone_number,new_phone_number,change_phone_story_map",
				"user_action_history"        => "row_id,user_id,type,created_at,extra",
				"session_history"            => "session_uniq,user_id,status,login_at,logout_at,ua_hash,ip_address,extra",
				"join_link_validate_history" => "history_id,join_link_uniq,user_id,session_uniq,input_link,created_at,extra",
				"join_link_accepted_history" => "history_id,join_link_uniq,user_id,company_id,entry_id,session_uniq,created_at,extra",
				"company_history"            => "log_id,company_id,type,created_at,extra",
				"send_history"               => "row_id,sms_id,is_success,task_created_at_ms,send_to_provider_at_ms,sms_sent_at_ms,created_at,provider_id,provider_response_code,provider_response,extra_alias",
			],
		],
	],

	"pivot_history_logs_2023" => [
		"db"      => "pivot_history_logs_2023",
		"mysql"   => [
			"host" => MYSQL_PIVOT_HISTORY_LOGS_HOST,
			"user" => MYSQL_PIVOT_HISTORY_LOGS_USER,
			"pass" => MYSQL_PIVOT_HISTORY_LOGS_PASS,
			"ssl"  => MYSQL_PIVOT_HISTORY_LOGS_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"user_auth_history"          => "auth_map,user_id,status,created_at,updated_at",
				"user_change_phone_history"  => "user_id,created_at,updated_at,previous_phone_number,new_phone_number,change_phone_story_map",
				"user_action_history"        => "row_id,user_id,type,created_at,extra",
				"session_history"            => "session_uniq,user_id,status,login_at,logout_at,ua_hash,ip_address,extra",
				"join_link_validate_history" => "history_id,join_link_uniq,user_id,session_uniq,input_link,created_at,extra",
				"join_link_accepted_history" => "history_id,join_link_uniq,user_id,company_id,entry_id,session_uniq,created_at,extra",
				"company_history"            => "log_id,company_id,type,created_at,extra",
				"send_history"               => "row_id,sms_id,is_success,task_created_at_ms,send_to_provider_at_ms,sms_sent_at_ms,created_at,provider_id,provider_response_code,provider_response,extra_alias",
			],
		],
	],

	"pivot_history_logs_2024" => [
		"db"      => "pivot_history_logs_2024",
		"mysql"   => [
			"host" => MYSQL_PIVOT_HISTORY_LOGS_HOST,
			"user" => MYSQL_PIVOT_HISTORY_LOGS_USER,
			"pass" => MYSQL_PIVOT_HISTORY_LOGS_PASS,
			"ssl"  => MYSQL_PIVOT_HISTORY_LOGS_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"user_auth_history"          => "auth_map,user_id,status,created_at,updated_at",
				"user_change_phone_history"  => "user_id,created_at,updated_at,previous_phone_number,new_phone_number,change_phone_story_map",
				"user_action_history"        => "row_id,user_id,type,created_at,extra",
				"session_history"            => "session_uniq,user_id,status,login_at,logout_at,ua_hash,ip_address,extra",
				"join_link_validate_history" => "history_id,join_link_uniq,user_id,session_uniq,input_link,created_at,extra",
				"join_link_accepted_history" => "history_id,join_link_uniq,user_id,company_id,entry_id,session_uniq,created_at,extra",
				"company_history"            => "log_id,company_id,type,created_at,extra",
				"send_history"               => "row_id,sms_id,is_success,task_created_at_ms,send_to_provider_at_ms,sms_sent_at_ms,created_at,provider_id,provider_response_code,provider_response,extra_alias",
			],
		],
	],

	"pivot_history_logs_2025" => [
		"db"      => "pivot_history_logs_2025",
		"mysql"   => [
			"host" => MYSQL_PIVOT_HISTORY_LOGS_HOST,
			"user" => MYSQL_PIVOT_HISTORY_LOGS_USER,
			"pass" => MYSQL_PIVOT_HISTORY_LOGS_PASS,
			"ssl"  => MYSQL_PIVOT_HISTORY_LOGS_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"user_auth_history"          => "auth_map,user_id,status,created_at,updated_at",
				"user_change_phone_history"  => "user_id,created_at,updated_at,previous_phone_number,new_phone_number,change_phone_story_map",
				"user_action_history"        => "row_id,user_id,type,created_at,extra",
				"session_history"            => "session_uniq,user_id,status,login_at,logout_at,ua_hash,ip_address,extra",
				"join_link_validate_history" => "history_id,join_link_uniq,user_id,session_uniq,input_link,created_at,extra",
				"join_link_accepted_history" => "history_id,join_link_uniq,user_id,company_id,entry_id,session_uniq,created_at,extra",
				"company_history"            => "log_id,company_id,type,created_at,extra",
				"send_history"               => "row_id,sms_id,is_success,task_created_at_ms,send_to_provider_at_ms,sms_sent_at_ms,created_at,provider_id,provider_response_code,provider_response,extra_alias",
			],
		],
	],

	"pivot_history_logs_2026" => [
		"db"      => "pivot_history_logs_2026",
		"mysql"   => [
			"host" => MYSQL_PIVOT_HISTORY_LOGS_HOST,
			"user" => MYSQL_PIVOT_HISTORY_LOGS_USER,
			"pass" => MYSQL_PIVOT_HISTORY_LOGS_PASS,
			"ssl"  => MYSQL_PIVOT_HISTORY_LOGS_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"user_auth_history"          => "auth_map,user_id,status,created_at,updated_at",
				"user_change_phone_history"  => "user_id,created_at,updated_at,previous_phone_number,new_phone_number,change_phone_story_map",
				"user_action_history"        => "row_id,user_id,type,created_at,extra",
				"session_history"            => "session_uniq,user_id,status,login_at,logout_at,ua_hash,ip_address,extra",
				"join_link_validate_history" => "history_id,join_link_uniq,user_id,session_uniq,input_link,created_at,extra",
				"join_link_accepted_history" => "history_id,join_link_uniq,user_id,company_id,entry_id,session_uniq,created_at,extra",
				"company_history"            => "log_id,company_id,type,created_at,extra",
				"send_history"               => "row_id,sms_id,is_success,task_created_at_ms,send_to_provider_at_ms,sms_sent_at_ms,created_at,provider_id,provider_response_code,provider_response,extra_alias",
			],
		],
	],

	"pivot_history_logs_2027" => [
		"db"      => "pivot_history_logs_2027",
		"mysql"   => [
			"host" => MYSQL_PIVOT_HISTORY_LOGS_HOST,
			"user" => MYSQL_PIVOT_HISTORY_LOGS_USER,
			"pass" => MYSQL_PIVOT_HISTORY_LOGS_PASS,
			"ssl"  => MYSQL_PIVOT_HISTORY_LOGS_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"user_auth_history"          => "auth_map,user_id,status,created_at,updated_at",
				"user_change_phone_history"  => "user_id,created_at,updated_at,previous_phone_number,new_phone_number,change_phone_story_map",
				"user_action_history"        => "row_id,user_id,type,created_at,extra",
				"session_history"            => "session_uniq,user_id,status,login_at,logout_at,ua_hash,ip_address,extra",
				"join_link_validate_history" => "history_id,join_link_uniq,user_id,session_uniq,input_link,created_at,extra",
				"join_link_accepted_history" => "history_id,join_link_uniq,user_id,company_id,entry_id,session_uniq,created_at,extra",
				"company_history"            => "log_id,company_id,type,created_at,extra",
				"send_history"               => "row_id,sms_id,is_success,task_created_at_ms,send_to_provider_at_ms,sms_sent_at_ms,created_at,provider_id,provider_response_code,provider_response,extra_alias",
			],
		],
	],

	"pivot_history_logs_2028" => [
		"db"      => "pivot_history_logs_2028",
		"mysql"   => [
			"host" => MYSQL_PIVOT_HISTORY_LOGS_HOST,
			"user" => MYSQL_PIVOT_HISTORY_LOGS_USER,
			"pass" => MYSQL_PIVOT_HISTORY_LOGS_PASS,
			"ssl"  => MYSQL_PIVOT_HISTORY_LOGS_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"user_auth_history"          => "auth_map,user_id,status,created_at,updated_at",
				"user_change_phone_history"  => "user_id,created_at,updated_at,previous_phone_number,new_phone_number,change_phone_story_map",
				"user_action_history"        => "row_id,user_id,type,created_at,extra",
				"session_history"            => "session_uniq,user_id,status,login_at,logout_at,ua_hash,ip_address,extra",
				"join_link_validate_history" => "history_id,join_link_uniq,user_id,session_uniq,input_link,created_at,extra",
				"join_link_accepted_history" => "history_id,join_link_uniq,user_id,company_id,entry_id,session_uniq,created_at,extra",
				"company_history"            => "log_id,company_id,type,created_at,extra",
				"send_history"               => "row_id,sms_id,is_success,task_created_at_ms,send_to_provider_at_ms,sms_sent_at_ms,created_at,provider_id,provider_response_code,provider_response,extra_alias",
			],
		],
	],

	##########################################################
	# endregion
	##########################################################

	##########################################################
	# region pivot_phone - база данных, хранящая телефоны
	##########################################################

	"pivot_phone" => [
		"db"      => "pivot_phone",
		"mysql"   => [
			"host" => MYSQL_PIVOT_PHONE_HOST,
			"user" => MYSQL_PIVOT_PHONE_USER,
			"pass" => MYSQL_PIVOT_PHONE_PASS,
			"ssl"  => MYSQL_PIVOT_PHONE_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"phone_change_story"         => "change_phone_story_id,user_id,status,stage,created_at,updated_at,expires_at,session_uniq",
				"phone_change_via_sms_story" => "change_phone_story_id,phone_number,status,stage,resend_count,error_count,created_at,updated_at,next_resend_at,sms_id,sms_code_hash",
				"phone_uniq_list_0"          => "phone_number_hash,user_id,binding_count,last_binding_at,last_unbinding_at,created_at,updated_at,previous_user_list",
				"phone_uniq_list_1"          => "phone_number_hash,user_id,binding_count,last_binding_at,last_unbinding_at,created_at,updated_at,previous_user_list",
				"phone_uniq_list_2"          => "phone_number_hash,user_id,binding_count,last_binding_at,last_unbinding_at,created_at,updated_at,previous_user_list",
				"phone_uniq_list_3"          => "phone_number_hash,user_id,binding_count,last_binding_at,last_unbinding_at,created_at,updated_at,previous_user_list",
				"phone_uniq_list_4"          => "phone_number_hash,user_id,binding_count,last_binding_at,last_unbinding_at,created_at,updated_at,previous_user_list",
				"phone_uniq_list_5"          => "phone_number_hash,user_id,binding_count,last_binding_at,last_unbinding_at,created_at,updated_at,previous_user_list",
				"phone_uniq_list_6"          => "phone_number_hash,user_id,binding_count,last_binding_at,last_unbinding_at,created_at,updated_at,previous_user_list",
				"phone_uniq_list_7"          => "phone_number_hash,user_id,binding_count,last_binding_at,last_unbinding_at,created_at,updated_at,previous_user_list",
				"phone_uniq_list_8"          => "phone_number_hash,user_id,binding_count,last_binding_at,last_unbinding_at,created_at,updated_at,previous_user_list",
				"phone_uniq_list_9"          => "phone_number_hash,user_id,binding_count,last_binding_at,last_unbinding_at,created_at,updated_at,previous_user_list",
				"phone_uniq_list_a"          => "phone_number_hash,user_id,binding_count,last_binding_at,last_unbinding_at,created_at,updated_at,previous_user_list",
				"phone_uniq_list_b"          => "phone_number_hash,user_id,binding_count,last_binding_at,last_unbinding_at,created_at,updated_at,previous_user_list",
				"phone_uniq_list_c"          => "phone_number_hash,user_id,binding_count,last_binding_at,last_unbinding_at,created_at,updated_at,previous_user_list",
				"phone_uniq_list_d"          => "phone_number_hash,user_id,binding_count,last_binding_at,last_unbinding_at,created_at,updated_at,previous_user_list",
				"phone_uniq_list_e"          => "phone_number_hash,user_id,binding_count,last_binding_at,last_unbinding_at,created_at,updated_at,previous_user_list",
				"phone_uniq_list_f"          => "phone_number_hash,user_id,binding_count,last_binding_at,last_unbinding_at,created_at,updated_at,previous_user_list",
			],
		],
	],

	##########################################################
	# endregion
	##########################################################

	##########################################################
	# region pivot_sms_service - база данных, для работы смс сервиса
	##########################################################

	"pivot_sms_service" => [
		"db"      => "pivot_sms_service",
		"mysql"   => [
			"host" => MYSQL_PIVOT_SMS_SERVICE_HOST,
			"user" => MYSQL_PIVOT_SMS_SERVICE_USER,
			"pass" => MYSQL_PIVOT_SMS_SERVICE_PASS,
			"ssl"  => MYSQL_PIVOT_SMS_SERVICE_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"send_queue"        => "sms_id,stage,need_work,error_count,created_at_ms,updated_at,expires_at,phone_number,provider_id,text,extra",
				"provider_list"     => "provider_id,is_available,is_deleted,created_at,updated_at,extra",
				"observer_provider" => "provider_id,need_work,created_at,extra",
			],
		],
	],

	##########################################################
	# endregion
	##########################################################

	##########################################################
	# region pivot_userbot - база данных, хранящая данные по ботам
	##########################################################

	"pivot_userbot" => [
		"db"      => "pivot_userbot",
		"mysql"   => [
			"host" => MYSQL_PIVOT_USERBOT_HOST,
			"user" => MYSQL_PIVOT_USERBOT_USER,
			"pass" => MYSQL_PIVOT_USERBOT_PASS,
			"ssl"  => MYSQL_PIVOT_USERBOT_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"userbot_list" => "userbot_id,company_id,status,user_id,created_at,updated_at,extra",
				"token_list"   => "token,userbot_id,created_at,updated_at,extra",
			],
		],
	],

	##########################################################
	# endregion
	##########################################################

	##########################################################
	# region partner_data – база данных, хранящая информацию по работе с партнерским ядром
	##########################################################

	"partner_data" => [
		"db"      => "partner_data",
		"mysql"   => [
			"host" => MYSQL_PIVOT_PARTNER_DATA_HOST,
			"user" => MYSQL_PIVOT_PARTNER_DATA_USER,
			"pass" => MYSQL_PIVOT_PARTNER_DATA_PASS,
			"ssl"  => MYSQL_PIVOT_PARTNER_DATA_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"invite_code_list" => "invite_code_hash,invite_code,partner_id,discount,can_reuse_after,expires_at,created_at,updated_at",
			],
		],
	],

	##########################################################
	# endregion
	##########################################################

	##########################################################
	# region partner_invite_link – база данных, хранящая ссылки созданные через партнеркую программу
	##########################################################

	"partner_invite_link" => [
		"db"      => "partner_invite_link",
		"mysql"   => [
			"host" => MYSQL_PARTNER_INVITE_LINK_HOST,
			"user" => MYSQL_PARTNER_INVITE_LINK_USER,
			"pass" => MYSQL_PARTNER_INVITE_LINK_PASS,
			"ssl"  => MYSQL_PARTNER_INVITE_LINK_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"invite_code_list_mirror" => "invite_code,partner_id,created_at",
			],
		],
	],

	##########################################################
	# endregion
	##########################################################

	##########################################################
	# region pivot_business – база данных, хранящая информацию для работы с bitrix
	##########################################################

	"pivot_business"   => [
		"db"      => "pivot_business",
		"mysql"   => [
			"host" => MYSQL_PIVOT_BUSINESS_HOST,
			"user" => MYSQL_PIVOT_BUSINESS_USER,
			"pass" => MYSQL_PIVOT_BUSINESS_PASS,
			"ssl"  => MYSQL_PIVOT_BUSINESS_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"bitrix_user_entity_rel"            => "user_id,created_at,updated_at,bitrix_entity_list",
				"bitrix_user_info_failed_task_list" => "task_id,user_id,failed_at",
			],
		],
	],

	##########################################################
	# endregion
	##########################################################

	##########################################################
	# region pivot_rating_{10m}
	##########################################################

	// база данных, хранящая рейтинг пользователей в приложении
	"pivot_rating_10m" => [
		"db"      => "pivot_rating_10m",
		"mysql"   => [
			"host" => MYSQL_PIVOT_USER_HOST,
			"user" => MYSQL_PIVOT_USER_USER,
			"pass" => MYSQL_PIVOT_USER_PASS,
			"ssl"  => MYSQL_PIVOT_USER_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"screen_time_raw_list_1"                => "user_id,space_id,user_local_time,screen_time,created_at",
				"screen_time_raw_list_2"                => "user_id,space_id,user_local_time,screen_time,created_at",
				"screen_time_raw_list_3"                => "user_id,space_id,user_local_time,screen_time,created_at",
				"screen_time_raw_list_4"                => "user_id,space_id,user_local_time,screen_time,created_at",
				"screen_time_raw_list_5"                => "user_id,space_id,user_local_time,screen_time,created_at",
				"screen_time_raw_list_6"                => "user_id,space_id,user_local_time,screen_time,created_at",
				"screen_time_raw_list_7"                => "user_id,space_id,user_local_time,screen_time,created_at",
				"screen_time_raw_list_8"                => "user_id,space_id,user_local_time,screen_time,created_at",
				"screen_time_raw_list_9"                => "user_id,space_id,user_local_time,screen_time,created_at",
				"screen_time_raw_list_10"               => "user_id,space_id,user_local_time,screen_time,created_at",
				"screen_time_user_day_list_1"           => "user_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_user_day_list_2"           => "user_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_user_day_list_3"           => "user_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_user_day_list_4"           => "user_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_user_day_list_5"           => "user_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_user_day_list_6"           => "user_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_user_day_list_7"           => "user_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_user_day_list_8"           => "user_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_user_day_list_9"           => "user_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_user_day_list_10"          => "user_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_space_day_list_1"          => "space_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_space_day_list_2"          => "space_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_space_day_list_3"          => "space_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_space_day_list_4"          => "space_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_space_day_list_5"          => "space_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_space_day_list_6"          => "space_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_space_day_list_7"          => "space_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_space_day_list_8"          => "space_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_space_day_list_9"          => "space_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_space_day_list_10"         => "space_id,user_local_date,created_at,updated_at,screen_time_list",
				"action_raw_list_1"                     => "user_id,space_id,action_at,created_at,action_list",
				"action_raw_list_2"                     => "user_id,space_id,action_at,created_at,action_list",
				"action_raw_list_3"                     => "user_id,space_id,action_at,created_at,action_list",
				"action_raw_list_4"                     => "user_id,space_id,action_at,created_at,action_list",
				"action_raw_list_5"                     => "user_id,space_id,action_at,created_at,action_list",
				"action_raw_list_6"                     => "user_id,space_id,action_at,created_at,action_list",
				"action_raw_list_7"                     => "user_id,space_id,action_at,created_at,action_list",
				"action_raw_list_8"                     => "user_id,space_id,action_at,created_at,action_list",
				"action_raw_list_9"                     => "user_id,space_id,action_at,created_at,action_list",
				"action_raw_list_10"                    => "user_id,space_id,action_at,created_at,action_list",
				"action_user_day_list_1"                => "user_id,day_start_at,created_at,updated_at,action_list",
				"action_user_day_list_2"                => "user_id,day_start_at,created_at,updated_at,action_list",
				"action_user_day_list_3"                => "user_id,day_start_at,created_at,updated_at,action_list",
				"action_user_day_list_4"                => "user_id,day_start_at,created_at,updated_at,action_list",
				"action_user_day_list_5"                => "user_id,day_start_at,created_at,updated_at,action_list",
				"action_user_day_list_6"                => "user_id,day_start_at,created_at,updated_at,action_list",
				"action_user_day_list_7"                => "user_id,day_start_at,created_at,updated_at,action_list",
				"action_user_day_list_8"                => "user_id,day_start_at,created_at,updated_at,action_list",
				"action_user_day_list_9"                => "user_id,day_start_at,created_at,updated_at,action_list",
				"action_user_day_list_10"               => "user_id,day_start_at,created_at,updated_at,action_list",
				"action_space_day_list_1"               => "space_id,day_start_at,created_at,updated_at,action_list",
				"action_space_day_list_2"               => "space_id,day_start_at,created_at,updated_at,action_list",
				"action_space_day_list_3"               => "space_id,day_start_at,created_at,updated_at,action_list",
				"action_space_day_list_4"               => "space_id,day_start_at,created_at,updated_at,action_list",
				"action_space_day_list_5"               => "space_id,day_start_at,created_at,updated_at,action_list",
				"action_space_day_list_6"               => "space_id,day_start_at,created_at,updated_at,action_list",
				"action_space_day_list_7"               => "space_id,day_start_at,created_at,updated_at,action_list",
				"action_space_day_list_8"               => "space_id,day_start_at,created_at,updated_at,action_list",
				"action_space_day_list_9"               => "space_id,day_start_at,created_at,updated_at,action_list",
				"action_space_day_list_10"              => "space_id,day_start_at,created_at,updated_at,action_list",
				"message_answer_time_raw_list_1"        => "user_id,answer_at,conversation_key,answer_time,space_id,created_at",
				"message_answer_time_raw_list_2"        => "user_id,answer_at,conversation_key,answer_time,space_id,created_at",
				"message_answer_time_raw_list_3"        => "user_id,answer_at,conversation_key,answer_time,space_id,created_at",
				"message_answer_time_raw_list_4"        => "user_id,answer_at,conversation_key,answer_time,space_id,created_at",
				"message_answer_time_raw_list_5"        => "user_id,answer_at,conversation_key,answer_time,space_id,created_at",
				"message_answer_time_raw_list_6"        => "user_id,answer_at,conversation_key,answer_time,space_id,created_at",
				"message_answer_time_raw_list_7"        => "user_id,answer_at,conversation_key,answer_time,space_id,created_at",
				"message_answer_time_raw_list_8"        => "user_id,answer_at,conversation_key,answer_time,space_id,created_at",
				"message_answer_time_raw_list_9"        => "user_id,answer_at,conversation_key,answer_time,space_id,created_at",
				"message_answer_time_raw_list_10"       => "user_id,answer_at,conversation_key,answer_time,space_id,created_at",
				"message_answer_time_user_day_list_1"   => "user_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_user_day_list_2"   => "user_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_user_day_list_3"   => "user_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_user_day_list_4"   => "user_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_user_day_list_5"   => "user_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_user_day_list_6"   => "user_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_user_day_list_7"   => "user_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_user_day_list_8"   => "user_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_user_day_list_9"   => "user_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_user_day_list_10"  => "user_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_space_day_list_1"  => "space_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_space_day_list_2"  => "space_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_space_day_list_3"  => "space_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_space_day_list_4"  => "space_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_space_day_list_5"  => "space_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_space_day_list_6"  => "space_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_space_day_list_7"  => "space_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_space_day_list_8"  => "space_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_space_day_list_9"  => "space_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_space_day_list_10" => "space_id,day_start_at,created_at,updated_at,answer_time_list",
			],
		],
	],

	# endregion
	##########################################################

	##########################################################
	# region pivot_rating_{20m}
	##########################################################

	// база данных, хранящая рейтинг пользователей в приложении
	"pivot_rating_20m" => [
		"db"      => "pivot_rating_20m",
		"mysql"   => [
			"host" => MYSQL_PIVOT_USER_HOST,
			"user" => MYSQL_PIVOT_USER_USER,
			"pass" => MYSQL_PIVOT_USER_PASS,
			"ssl"  => MYSQL_PIVOT_USER_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"screen_time_raw_list_11"               => "user_id,space_id,user_local_time,screen_time,created_at",
				"screen_time_raw_list_12"               => "user_id,space_id,user_local_time,screen_time,created_at",
				"screen_time_raw_list_13"               => "user_id,space_id,user_local_time,screen_time,created_at",
				"screen_time_raw_list_14"               => "user_id,space_id,user_local_time,screen_time,created_at",
				"screen_time_raw_list_15"               => "user_id,space_id,user_local_time,screen_time,created_at",
				"screen_time_raw_list_16"               => "user_id,space_id,user_local_time,screen_time,created_at",
				"screen_time_raw_list_17"               => "user_id,space_id,user_local_time,screen_time,created_at",
				"screen_time_raw_list_18"               => "user_id,space_id,user_local_time,screen_time,created_at",
				"screen_time_raw_list_19"               => "user_id,space_id,user_local_time,screen_time,created_at",
				"screen_time_raw_list_20"               => "user_id,space_id,user_local_time,screen_time,created_at",
				"screen_time_user_day_list_11"          => "user_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_user_day_list_12"          => "user_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_user_day_list_13"          => "user_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_user_day_list_14"          => "user_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_user_day_list_15"          => "user_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_user_day_list_16"          => "user_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_user_day_list_17"          => "user_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_user_day_list_18"          => "user_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_user_day_list_19"          => "user_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_user_day_list_20"          => "user_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_space_day_list_11"         => "space_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_space_day_list_12"         => "space_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_space_day_list_13"         => "space_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_space_day_list_14"         => "space_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_space_day_list_15"         => "space_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_space_day_list_16"         => "space_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_space_day_list_17"         => "space_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_space_day_list_18"         => "space_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_space_day_list_19"         => "space_id,user_local_date,created_at,updated_at,screen_time_list",
				"screen_time_space_day_list_20"         => "space_id,user_local_date,created_at,updated_at,screen_time_list",
				"action_raw_list_11"                    => "user_id,space_id,action_at,created_at,action_list",
				"action_raw_list_12"                    => "user_id,space_id,action_at,created_at,action_list",
				"action_raw_list_13"                    => "user_id,space_id,action_at,created_at,action_list",
				"action_raw_list_14"                    => "user_id,space_id,action_at,created_at,action_list",
				"action_raw_list_15"                    => "user_id,space_id,action_at,created_at,action_list",
				"action_raw_list_16"                    => "user_id,space_id,action_at,created_at,action_list",
				"action_raw_list_17"                    => "user_id,space_id,action_at,created_at,action_list",
				"action_raw_list_18"                    => "user_id,space_id,action_at,created_at,action_list",
				"action_raw_list_19"                    => "user_id,space_id,action_at,created_at,action_list",
				"action_raw_list_20"                    => "user_id,space_id,action_at,created_at,action_list",
				"action_user_day_list_11"               => "user_id,day_start_at,created_at,updated_at,action_list",
				"action_user_day_list_12"               => "user_id,day_start_at,created_at,updated_at,action_list",
				"action_user_day_list_13"               => "user_id,day_start_at,created_at,updated_at,action_list",
				"action_user_day_list_14"               => "user_id,day_start_at,created_at,updated_at,action_list",
				"action_user_day_list_15"               => "user_id,day_start_at,created_at,updated_at,action_list",
				"action_user_day_list_16"               => "user_id,day_start_at,created_at,updated_at,action_list",
				"action_user_day_list_17"               => "user_id,day_start_at,created_at,updated_at,action_list",
				"action_user_day_list_18"               => "user_id,day_start_at,created_at,updated_at,action_list",
				"action_user_day_list_19"               => "user_id,day_start_at,created_at,updated_at,action_list",
				"action_user_day_list_20"               => "user_id,day_start_at,created_at,updated_at,action_list",
				"action_space_day_list_11"              => "space_id,day_start_at,created_at,updated_at,action_list",
				"action_space_day_list_12"              => "space_id,day_start_at,created_at,updated_at,action_list",
				"action_space_day_list_13"              => "space_id,day_start_at,created_at,updated_at,action_list",
				"action_space_day_list_14"              => "space_id,day_start_at,created_at,updated_at,action_list",
				"action_space_day_list_15"              => "space_id,day_start_at,created_at,updated_at,action_list",
				"action_space_day_list_16"              => "space_id,day_start_at,created_at,updated_at,action_list",
				"action_space_day_list_17"              => "space_id,day_start_at,created_at,updated_at,action_list",
				"action_space_day_list_18"              => "space_id,day_start_at,created_at,updated_at,action_list",
				"action_space_day_list_19"              => "space_id,day_start_at,created_at,updated_at,action_list",
				"action_space_day_list_20"              => "space_id,day_start_at,created_at,updated_at,action_list",
				"message_answer_time_raw_list_11"       => "user_id,answer_at,conversation_key,answer_time,space_id,created_at",
				"message_answer_time_raw_list_12"       => "user_id,answer_at,conversation_key,answer_time,space_id,created_at",
				"message_answer_time_raw_list_13"       => "user_id,answer_at,conversation_key,answer_time,space_id,created_at",
				"message_answer_time_raw_list_14"       => "user_id,answer_at,conversation_key,answer_time,space_id,created_at",
				"message_answer_time_raw_list_15"       => "user_id,answer_at,conversation_key,answer_time,space_id,created_at",
				"message_answer_time_raw_list_16"       => "user_id,answer_at,conversation_key,answer_time,space_id,created_at",
				"message_answer_time_raw_list_17"       => "user_id,answer_at,conversation_key,answer_time,space_id,created_at",
				"message_answer_time_raw_list_18"       => "user_id,answer_at,conversation_key,answer_time,space_id,created_at",
				"message_answer_time_raw_list_19"       => "user_id,answer_at,conversation_key,answer_time,space_id,created_at",
				"message_answer_time_raw_list_20"       => "user_id,answer_at,conversation_key,answer_time,space_id,created_at",
				"message_answer_time_user_day_list_11"  => "user_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_user_day_list_12"  => "user_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_user_day_list_13"  => "user_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_user_day_list_14"  => "user_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_user_day_list_15"  => "user_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_user_day_list_16"  => "user_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_user_day_list_17"  => "user_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_user_day_list_18"  => "user_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_user_day_list_19"  => "user_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_user_day_list_20"  => "user_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_space_day_list_11" => "space_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_space_day_list_12" => "space_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_space_day_list_13" => "space_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_space_day_list_14" => "space_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_space_day_list_15" => "space_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_space_day_list_16" => "space_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_space_day_list_17" => "space_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_space_day_list_18" => "space_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_space_day_list_19" => "space_id,day_start_at,created_at,updated_at,answer_time_list",
				"message_answer_time_space_day_list_20" => "space_id,day_start_at,created_at,updated_at,answer_time_list",
			],
		],
	],

	# endregion
	##########################################################

	##########################################################
	# region pivot_attribution – база данных для атрибуции установок
	##########################################################

	"pivot_attribution" => [
		"db"      => "pivot_attribution",
		"mysql"   => [
			"host" => MYSQL_PIVOT_ATTRIBUTION_HOST,
			"user" => MYSQL_PIVOT_ATTRIBUTION_USER,
			"pass" => MYSQL_PIVOT_ATTRIBUTION_PASS,
			"ssl"  => MYSQL_PIVOT_ATTRIBUTION_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"landing_visit_log"         => "visit_id,guest_id,link,utm_tag,source_id,ip_address,platform,platform_os,timezone_utc_offset,screen_avail_width,screen_avail_height,visited_at,created_at",
				"user_app_registration_log" => "user_id,ip_address,platform,platform_os,timezone_utc_offset,screen_avail_width,screen_avail_height,registered_at,created_at",
				"user_campaign_rel"         => "user_id,visit_id,utm_tag,source_id,link,is_direct_reg,created_at",
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
	"pivot_cache"     => [
		"host" => GO_PIVOT_CACHE_GRPC_HOST,
		"port" => GO_PIVOT_CACHE_GRPC_PORT,
	],
	"pusher"          => [
		"host" => GO_PUSHER_HOST,
		"port" => GO_PUSHER_GRPC_PORT,
	],
	"sender_balancer" => [
		"host" => GO_SENDER_BALANCER_GRPC_HOST,
		"port" => GO_SENDER_BALANCER_GRPC_PORT,
		"url"  => PUBLIC_WEBSOCKET_PIVOT,
	],
	"collector_agent" => [
		"protocol" => GO_COLLECTOR_AGENT_PROTOCOL,
		"host"     => GO_COLLECTOR_AGENT_HOST,
		"port"     => GO_COLLECTOR_AGENT_HTTP_PORT,
	],
	"event"           => [
		"host" => GO_EVENT_GRPC_HOST,
		"port" => GO_EVENT_GRPC_PORT,
	],
];

$CONFIG["SHARDING_MCACHE"] = [
	"host" => MCACHE_HOST,
	"port" => MCACHE_PORT,
];

return $CONFIG;