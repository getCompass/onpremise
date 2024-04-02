<?php

namespace Compass\Federation;

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

	"sso_data" => [
		"db"      => "sso_data",
		"mysql"   => [
			"host" => MYSQL_FEDERATION_SSO_HOST,
			"user" => MYSQL_FEDERATION_SSO_USER,
			"pass" => MYSQL_FEDERATION_SSO_PASS,
			"ssl"  => MYSQL_FEDERATION_SSO_SSL,
		],
		"schemas" => [
			"sharding_info" => [
				"type" => \shardingConf::SHARDING_TYPE_NONE,
				"data" => [],
			],
			"tables"        => [
				"sso_auth_list"               => "sso_auth_token,signature,status,expires_at,completed_at,created_at,updated_at,link,ua_hash,ip_address",
				"sso_account_oidc_token_list" => "row_id,sub_hash,sso_auth_token,expires_at,last_refresh_at,created_at,updated_at,data",
				"sso_account_user_rel"        => "sub_hash,user_id,sub_plain,created_at",
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

$CONFIG["SHARDING_GO"] = [];

$CONFIG["SHARDING_MCACHE"] = [
	"host" => MCACHE_HOST,
	"port" => MCACHE_PORT,
];

return $CONFIG;