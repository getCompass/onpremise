<?php

// @formatter:off
// ----
// private/main.php
// авторизационные данные для подключения к MySQL/RabbitMQ/mCache и т/п
// ----

##########################################################
# region MYSQL
##########################################################

define("MYSQL_HOST"					, "${MYSQL_HOST}:${MYSQL_PORT}");
define("MYSQL_PORT"					, "${MYSQL_PORT}");
define("MYSQL_USER"					, "${MYSQL_ROOT_USER}");
define("MYSQL_PASS"					, "${MYSQL_ROOT_PASS}");
define("MYSQL_SSL"					, false);

// system db
define("MYSQL_SYSTEM_HOST"			, "${MYSQL_HOST}");
define("MYSQL_SYSTEM_PORT"			, "${MYSQL_PORT}");
define("MYSQL_SYSTEM_USER"			, "${MYSQL_SYSTEM_USER}");
define("MYSQL_SYSTEM_PASS"			, "${MYSQL_PASS}");

# endregion
##########################################################

##########################################################
# region Service
##########################################################

define("RABBIT_HOST"				, "${RABBIT_HOST}");
define("RABBIT_PORT"				, "${RABBIT_PORT}");
define("RABBIT_USER"				, "${RABBIT_USER}");
define("RABBIT_PASS"				, "${RABBIT_PASS}");
define("RABBIT_QUEUES_COUNT"			, ${RABBIT_QUEUES_COUNT});

// RABBIT BUS
define("RABBIT_BUS_HOST"				, "${RABBIT_HOST}");
define("RABBIT_BUS_PORT"				, "${RABBIT_PORT}");
define("RABBIT_BUS_USER"				, "${RABBIT_USER}");
define("RABBIT_BUS_PASS"				, "${RABBIT_PASS}");

define("MCACHE_HOST"				, "${MCACHE_HOST}");
define("MCACHE_PORT"				, "${MCACHE_PORT}");

define("MANTICORE_HOST"				, "${MANTICORE_HOST}");
define("MANTICORE_PORT"				, ${MANTICORE_PORT});

# endregion
##########################################################

##########################################################
# region GOLANG
##########################################################

define("GO_PIVOT_CACHE_GRPC_HOST"         , "${GO_PIVOT_GRPC_HOST}");
define("GO_PIVOT_CACHE_GRPC_PORT"         , "${GO_PIVOT_GRPC_PORT}");

define("GO_COMPANY_CACHE_GRPC_HOST"		, "${GO_COMPANY_CACHE_HOST}");
define("GO_COMPANY_CACHE_GRPC_PORT"		,  ${GO_COMPANY_CACHE_GRPC_PORT});

define("GO_COMPANY_GRPC_HOST"			, "${GO_COMPANY_HOST}");
define("GO_COMPANY_GRPC_PORT"			,  ${GO_COMPANY_GRPC_PORT});
define("GO_COMPANY_QUEUE"			, "${GO_COMPANY_QUEUE}");
define("GO_COMPANY_EXCHANGE"			, "${GO_COMPANY_EXCHANGE}");

define("GO_SENDER_BALANCER_GRPC_HOST"			, "${GO_SENDER_BALANCER_GRPC_HOST}");
define("GO_SENDER_BALANCER_GRPC_PORT"		, ${GO_SENDER_BALANCER_GRPC_PORT});
define("GO_SENDER_BALANCER_URL"			, "wss://${PIVOT_DOMAIN}/ws");
define("GO_SENDER_BALANCER_QUEUE"			, "${GO_SENDER_BALANCER_QUEUE}");

define("GO_SENDER_GRPC_HOST"			, "${GO_SENDER_HOST}");
define("GO_SENDER_GRPC_PORT"			, "${GO_SENDER_GRPC_PORT}");
define("GO_SENDER_URL"				, CompassApp\System\Sharding::getSenderWsUrl());

define("GO_COLLECTOR_AGENT_PROTOCOL"	, "${GO_COLLECTOR_AGENT_PROTOCOL}");
define("GO_COLLECTOR_AGENT_HOST"		, "${GO_COLLECTOR_AGENT_HOST}");
define("GO_COLLECTOR_AGENT_HTTP_PORT"	, "${GO_COLLECTOR_AGENT_HTTP_PORT}");

define("GO_PARTNER_AGENT_PROTOCOL"		, "${GO_PARTNER_AGENT_PROTOCOL}");
define("GO_PARTNER_AGENT_HOST"		, "${GO_PARTNER_AGENT_HOST}");
define("GO_PARTNER_AGENT_HTTP_PORT"	      , "${GO_PARTNER_AGENT_HTTP_PORT}");

define("GO_EVENT_GRPC_HOST"               , "${GO_EVENT_HOST}");
define("GO_EVENT_GRPC_PORT"               , "${GO_EVENT_PORT}");
define("GO_EVENT_GLOBAL_EVENT_QUEUE"      , "${GO_EVENT_GLOBAL_EVENT_QUEUE}");
define("GO_EVENT_SERVICE_EVENT_EXCHANGE"  , "${GO_EVENT_SERVICE_EVENT_EXCHANGE}");

define("GO_RATING_GRPC_HOST"              , "${GO_RATING_HOST}");
define("GO_RATING_GRPC_PORT"              , "${GO_RATING_GRPC_PORT}");

define("GO_MOCK_SERVICE_URL"			, "${GO_MOCK_SERVICE_URL}");
define("GO_TEST_URL"               		, "${GO_TEST_URL}");

define("GO_ACTIVITY_GRPC_HOST"		, "${GO_ACTIVITY_HOST}");
define("GO_ACTIVITY_GRPC_PORT"		, ${GO_ACTIVITY_GRPC_PORT});

# endregion
##########################################################
// @formatter:on
