<?php

// @formatter:off
// ----
// private/main.php
// авторизационные данные для подключения к MySQL/RabbitMQ/mCache и т/п
// ----

if (CURRENT_SERVER == \Compass\FileBalancer\PIVOT_SERVER) {

	define("MYSQL_FILE_HOST"			, "${MYSQL_HOST}:${MYSQL_PORT}");
	define("MYSQL_FILE_USER"			, "${MYSQL_USER}");
	define("MYSQL_FILE_PASS"			, "${MYSQL_PASS}");
	define("MYSQL_FILE_SSL"				, false);
}

// @formatter:on
