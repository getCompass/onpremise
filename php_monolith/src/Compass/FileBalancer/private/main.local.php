<?php

namespace Compass\FileBalancer;

// @formatter:off
// ----
// private/main.php
// авторизационные данные для подключения к MySQL/RabbitMQ/mCache и т/п
// ----

if (CURRENT_SERVER === PIVOT_SERVER) {

	define(__NAMESPACE__ . "\MYSQL_FILE_HOST"			, "${MYSQL_HOST}:${MYSQL_PORT}");
	define(__NAMESPACE__ . "\MYSQL_FILE_USER"			, "${MYSQL_USER}");
	define(__NAMESPACE__ . "\MYSQL_FILE_PASS"			, "${MYSQL_PASS}");
	define(__NAMESPACE__ . "\MYSQL_FILE_SSL"			, false);
}

// @formatter:on
