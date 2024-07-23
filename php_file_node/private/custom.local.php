<?php

// @formatter:off
// ----
// private/custom.php
// приватные константы, ключи шифрования/доступа к сервисам
// ----

// имя сервера и его тип
define("SERVER_NAME"						, "${SERVER_NAME}");
define("SERVER_TAG_LIST"					, ${SERVER_TAG_LIST});

// api readonly
define("IS_READONLY"				 		, ${IS_READONLY});

define("DISPLAY_ERRORS"						, ${DISPLAY_ERRORS});

// константы для того чтобы знать адрес сервера
define("PIVOT_PROTOCOL"						, "${PROTOCOL}");
define("PIVOT_DOMAIN"						, "${PIVOT_DOMAIN}");

// константа которая указывает на путь к куки
define("COMPANY_PROTOCOL"					, "${PROTOCOL}");
define("COMPANY_DOMAIN"						, "${COMPANY_DOMAIN}");

// константы для канала уведомлений
define("NOTICE_ENDPOINT"                  		, "${NOTICE_ENDPOINT}");
define("NOTICE_BOT_USER_ID"               		, "${NOTICE_BOT_USER_ID}");
define("NOTICE_BOT_TOKEN"                 		, "${NOTICE_BOT_TOKEN}");
define("NOTICE_CHANNEL_EXCEPTION"     			, "${NOTICE_CHANNEL_EXCEPTION}");

// -------------------------------------------------------
// ШИФРОВАНИЕ
// -------------------------------------------------------

// для всего остального - один
define("ENCRYPT_KEY_DEFAULT"           		      , "${ENCRYPT_KEY_DEFAULT}"); // ключ
define("ENCRYPT_IV_DEFAULT"            		      , "${ENCRYPT_IV_DEFAULT}"); // вектор шифрования

// -------------------------------------------------------
// SALT ДЛЯ УПАКОВЩИКОВ ПРОЕКТА
// -------------------------------------------------------

// соль для формирования file_map
define("SALT_PACK_FILE"				, [
	1	=>	"${SALT_PACK_FILE_1}",
	2	=>	"${SALT_PACK_FILE_2}",
	3	=>	"${SALT_PACK_FILE_3}",
]);

// -------------------------------------------------------
// КЛЮЧИ SOCKET для приложений
// -------------------------------------------------------

define("ENTRYPOINT_PIVOT"		      , "${ENTRYPOINT_PIVOT}");
define("ENTRYPOINT_FILE_NODE"		      , "${ENTRYPOINT_FILE_NODE}");

define("PUBLIC_ENTRYPOINT_PIVOT"          , "${PUBLIC_ENTRYPOINT_PIVOT}");
define("PUBLIC_ENTRYPOINT_START"          , "${PUBLIC_ENTRYPOINT_START}");

define("SOCKET_KEY_ME"                    , "${SOCKET_KEY_FILE_NODE}");
define("SOCKET_KEY_PIVOT"			, "${SOCKET_KEY_PIVOT}");
define("SOCKET_KEY_CONVERSATION"		, "${SOCKET_KEY_CONVERSATION}");
define("SOCKET_KEY_THREAD"		      , "${SOCKET_KEY_THREAD}");
define("SOCKET_KEY_FILE_BALANCER"		, "${SOCKET_KEY_FILE_BALANCER}");
define("SOCKET_KEY_INTERCOM"			, "${SOCKET_KEY_INTERCOM}");

// -------------------------------------------------------
// NODE
// -------------------------------------------------------

define("NODE_ID"                          , "${NODE_ID}");
define("NODE_URL"                         , "${NODE_URL}");
define("CDN_URL"                          , "${CDN_URL}");

// путь до access-лога nginx
define("LOG_FILE_PATH"				, PATH_LOGS . "nginx/access.log");

define("SALT_ANALYTIC"                    , "${SALT_ANALYTIC}");

// количество потоков видео (обычно равно количеству ядер процессора
define("VIDEO_PROCESS_THREAD_COUNT"		, "${VIDEO_PROCESS_THREAD_COUNT}");

define("INTEGRATION_AUTHORIZATION_TOKEN"  , "${INTEGRATION_AUTHORIZATION_TOKEN}");

// @formatter:on <чтобы автоформатирование не убирало табы>
