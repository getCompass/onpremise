<?php

namespace Compass\Conversation;

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

		"company_system" => [
			"db"      => "company_system",
			"mysql"   => [
				"host" => $company_mysql_host . ":" . $company_mysql_port,
				"user" => $company_mysql_user,
				"pass" => $company_mysql_pass,
				"ssl"  => false,
			],
			"schemas" => [],
		],

		"company_conversation" => [
			"db"      => "company_conversation",
			"mysql"   => [
				"host" => $company_mysql_host . ":" . $company_mysql_port,
				"user" => $company_mysql_user,
				"pass" => $company_mysql_pass,
				"ssl"  => false,
			],
			"schemas" => [],
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

		"company_temp" => [
			"db"      => "company_temp",
			"mysql"   => [
				"host" => $company_mysql_host . ":" . $company_mysql_port,
				"user" => $company_mysql_user,
				"pass" => $company_mysql_pass,
				"ssl"  => false,
			],
			"schemas" => [],
		],

		"space_search" => [
			"db"      => "space_search",
			"mysql"   => [
				"host" => $company_mysql_host . ":" . $company_mysql_port,
				"user" => $company_mysql_user,
				"pass" => $company_mysql_pass,
				"ssl"  => false,
			],
			"schemas" => [],
		],
	];
}
// -------------------------------------------------------
// GOLANG
// -------------------------------------------------------

// go_session - предпалагает sharding
$CONFIG["SHARDING_GO"] = [

	// для общения пользователей по websocket
	"sender"          => [
		"host" => GO_SENDER_GRPC_HOST,
		"port" => GO_SENDER_GRPC_PORT,
		"url"  => GO_SENDER_URL,
	],
	"company_cache"   => [
		"host" => GO_COMPANY_CACHE_GRPC_HOST,
		"port" => GO_COMPANY_CACHE_GRPC_PORT,
	],
	"company"         => [
		"host" => GO_COMPANY_GRPC_HOST,
		"port" => GO_COMPANY_GRPC_PORT,
	],
	"collector_agent" => [
		"protocol" => GO_COLLECTOR_AGENT_PROTOCOL,
		"host"     => GO_COLLECTOR_AGENT_HOST,
		"port"     => GO_COLLECTOR_AGENT_HTTP_PORT,
	],
	"partner_agent"   => [
		"protocol" => GO_PARTNER_AGENT_PROTOCOL,
		"host"     => GO_PARTNER_AGENT_HOST,
		"port"     => GO_PARTNER_AGENT_HTTP_PORT,
	],
	"event"           => [
		"host" => GO_EVENT_GRPC_HOST,
		"port" => GO_EVENT_GRPC_PORT,
	],
	"rating"          => [
		"host" => GO_RATING_GRPC_HOST,
		"port" => GO_RATING_GRPC_PORT,
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

// -------------------------------------------------------
// MCACHE
// -------------------------------------------------------

$CONFIG["SHARDING_MCACHE"] = [
	"host" => MCACHE_HOST,
	"port" => MCACHE_PORT,
];

// -------------------------------------------------------
// Manticore Search
// -------------------------------------------------------

$CONFIG["SHARDING_MANTICORE"] = [
	"host" => MANTICORE_HOST,
	"port" => MANTICORE_PORT,
];

return $CONFIG;