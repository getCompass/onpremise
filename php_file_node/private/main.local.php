<?php

// @formatter:off
// ----
// private/main.php
// авторизационные данные для подключения к MySQL/RabbitMQ/mCache и т/п
// ----

##########################################################
# region MySQL
##########################################################

// название базы, на случай если на одной тачке 2 файловые ноды
// !!!! не забудь сменить название в install.sql / active.sql
define("MYSQL_FILE_NODE_HOST"			, "${MYSQL_HOST}:${MYSQL_PORT}");
define("MYSQL_FILE_NODE_USER"			, "${MYSQL_USER}");
define("MYSQL_FILE_NODE_PASS"			, "${MYSQL_PASS}");
define("MYSQL_FILE_NODE_SSL"			, false);

// system db
define("MYSQL_SYSTEM_HOST"			, "${MYSQL_SYSTEM_HOST}:${MYSQL_SYSTEM_PORT}");
define("MYSQL_SYSTEM_USER"			, "${MYSQL_SYSTEM_USER}");
define("MYSQL_SYSTEM_PASS"			, "${MYSQL_SYSTEM_PASS}");

# endregion
##########################################################

// RABBIT LOCAL
define("RABBIT_HOST"					, "${RABBIT_HOST}");
define("RABBIT_PORT"					, "${RABBIT_PORT}");
define("RABBIT_USER"					, "${RABBIT_USER}");
define("RABBIT_PASS"					, "${RABBIT_PASS}");

// RABBIT BUS
define("RABBIT_BUS_HOST"				, "${RABBIT_HOST}");
define("RABBIT_BUS_PORT"				, "${RABBIT_PORT}");
define("RABBIT_BUS_USER"				, "${RABBIT_USER}");
define("RABBIT_BUS_PASS"				, "${RABBIT_PASS}");

// MEMCACHED
define("MCACHE_HOST"					, "${MCACHE_HOST}");
define("MCACHE_PORT"					, "${MCACHE_PORT}");

define("GO_COLLECTOR_AGENT_PROTOCOL"		, "${GO_COLLECTOR_AGENT_PROTOCOL}");
define("GO_COLLECTOR_AGENT_HOST"			, "${GO_COLLECTOR_AGENT_HOST}");
define("GO_COLLECTOR_AGENT_HTTP_PORT"		, ${GO_COLLECTOR_AGENT_HTTP_PORT});

# endregion
##########################################################

// @formatter:on
