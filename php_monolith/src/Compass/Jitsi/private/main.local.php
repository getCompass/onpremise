<?php

namespace Compass\Jitsi;

##########################################################
# region MySQL
##########################################################

define(__NAMESPACE__ . "\MYSQL_JITSI_DATA_HOST"			, "${MYSQL_HOST}:${MYSQL_PORT}");
define(__NAMESPACE__ . "\MYSQL_JITSI_DATA_USER"			, "${MYSQL_USER}");
define(__NAMESPACE__ . "\MYSQL_JITSI_DATA_PASS"			, "${MYSQL_PASS}");
define(__NAMESPACE__ . "\MYSQL_JITSI_DATA_SSL"				, false);

define(__NAMESPACE__ . "\MYSQL_SYSTEM_HOST"				, "${MYSQL_HOST}:${MYSQL_PORT}");
define(__NAMESPACE__ . "\MYSQL_SYSTEM_USER"				, "${MYSQL_USER}");
define(__NAMESPACE__ . "\MYSQL_SYSTEM_PASS"				, "${MYSQL_PASS}");
define(__NAMESPACE__ . "\MYSQL_SYSTEM_SSL"				, false);

define(__NAMESPACE__ . "\MYSQL_COMPANY_HOST"				, "${MYSQL_HOST}:${MYSQL_PORT}");
define(__NAMESPACE__ . "\MYSQL_COMPANY_USER"				, "${MYSQL_USER}");
define(__NAMESPACE__ . "\MYSQL_COMPANY_PASS"				, "${MYSQL_PASS}");
define(__NAMESPACE__ . "\MYSQL_COMPANY_SSL"				, false);

define(__NAMESPACE__ . "\GO_EVENT_HOST"					, "${GO_EVENT_HOST}");
define(__NAMESPACE__ . "\GO_EVENT_PORT"					, ${GO_EVENT_GRPC_PORT});

# endregion
##########################################################