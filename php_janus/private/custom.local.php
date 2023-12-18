<?php

/** @noinspection PhpUndefinedConstantInspection */
/** @noinspection PhpUndefinedVariableInspection */

//@formatter:off

// ----
// private/custom.php
// приватные константы, ключи шифрования/доступа к сервисам
// ----

// служебные константы
define("SERVER_NAME"				, "${SERVER_NAME}");
define("SERVER_TAG_LIST"			, ${SERVER_TAG_LIST});
define("DEV_SERVER"					, ${DEV_SERVER});           // false on public
define("DISPLAY_ERRORS"				, ${DISPLAY_ERRORS});       // false on public

define("PROTOCOL"					, "${PROTOCOL}");
define("DOMAIN"						, "${JANUS_DOMAIN}");

define("CURRENT_MODULE"				, "php_janus");

// константы для уведомлений
define("NOTICE_ENDPOINT"			, "${NOTICE_ENDPOINT}");
define("NOTICE_BOT_USER_ID"			, "${NOTICE_BOT_USER_ID}");
define("NOTICE_BOT_TOKEN"			, "${NOTICE_BOT_TOKEN}");
define("NOTICE_CHANNEL_KEY"			, "${NOTICE_CHANNEL_KEY}");

// -------------------------------------------------------
// ДАННЫЕ ДЛЯ СОКЕТ-ЗАПРОСОВ
// -------------------------------------------------------

define("SOCKET_KEY_ME"				, "${SOCKET_KEY_ANNOUNCEMENT}");

//@formatter:on