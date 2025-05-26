<?php

// @formatter:off
// ----
// private/custom.php
// приватные константы, ключи шифрования/доступа к сервисам
// ----

// имя сервера и его тип
define("SERVER_NAME"				, "${SERVER_NAME}");
define("SERVER_TAG_LIST"			, ${SERVER_TAG_LIST});
define("DISPLAY_ERRORS"				, ${DISPLAY_ERRORS});            // false on public

// константы для того чтобы знать адрес сервера
define("PIVOT_PROTOCOL"				, "${PROTOCOL}");
define("PIVOT_DOMAIN"				, "${DOMAIN}");
define("DOMINO_ID"				, "${DOMINO_ID}");

// константы для уведомлений
define("NOTICE_ENDPOINT"                  , "${NOTICE_ENDPOINT}");
define("NOTICE_BOT_USER_ID"               , "${NOTICE_BOT_USER_ID}");
define("NOTICE_BOT_TOKEN"                 , "${NOTICE_BOT_TOKEN}");
define("NOTICE_CHANNEL_SERVICE"     	, "${NOTICE_CHANNEL_SERVICE}");

define("ENTRYPOINT_PIVOT"		      , "${ENTRYPOINT_PIVOT}");

// ключи для сокет запросов с пивотом
define("SOCKET_KEY_MIGRATION"			, "${SOCKET_KEY_MIGRATION}");

define("CA_CERTIFICATE"                	, "${CA_CERTIFICATE}");

// @formatter:on