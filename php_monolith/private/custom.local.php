<?php

// @formatter:off
// ----
// private/custom.php
// приватные константы, ключи шифрования/доступа к сервисам
// ----

// служебные константы
define("SERVER_NAME"				, "${SERVER_NAME}");    // имя сервера
define("SERVER_TAG_LIST"			, ${SERVER_TAG_LIST});
define("DEV_SERVER"				, "${DEV_SERVER}");     // false on public
define("DISPLAY_ERRORS"				, ${DISPLAY_ERRORS}); // false on public

// константы для того чтобы знать адрес сервера
define("PIVOT_PROTOCOL"				, "${PROTOCOL}");
define("PIVOT_DOMAIN"				, "${DOMAIN}");

define("INTERCOM_PROTOCOL"			, "${INTERCOM_PROTOCOL}");
define("INTERCOM_DOMAIN"			, "${INTERCOM_DOMAIN}");

define("ANNOUNCEMENT_DOMAIN"			, "${ANNOUNCEMENT_DOMAIN}");

define("DOMINO_ID"				, "${DOMINO_ID}");

// -------------------------------------------------------
// noticebot
// -------------------------------------------------------

// константы для уведомлений
define("NOTICE_ENDPOINT"                  , "${NOTICE_ENDPOINT}");
define("NOTICE_BOT_USER_ID"               , "${NOTICE_BOT_USER_ID}");
define("NOTICE_BOT_TOKEN"                 , "${NOTICE_BOT_TOKEN}");
define("NOTICE_CHANNEL_KEY"     		, "${NOTICE_CHANNEL_KEY}");

// -------------------------------------------------------
// КЛЮЧИ ШИФРОВАНИЯ OPENSSL
// -------------------------------------------------------

define("ENCRYPT_IV_ACTION"			, "${ENCRYPT_IV_ACTION}");
define("ENCRYPT_PASSPHRASE_ACTION"        , "${ENCRYPT_PASSPHRASE_ACTION}");

// для SESSION - отдельный
define("ENCRYPT_KEY_COMPANY_SESSION"	, "${ENCRYPT_KEY_COMPANY_SESSION}"); // ключ шифрования для сущности session_key
define("ENCRYPT_IV_COMPANY_SESSION"		, "${ENCRYPT_IV_COMPANY_SESSION}");  // вектор шифрования для сущности session_key

// для SESSION - отдельный
define("ENCRYPT_KEY_PIVOT_SESSION"                    , "${ENCRYPT_KEY_PIVOT_SESSION}"); // ключ шифрования для сущности session_key
define("ENCRYPT_IV_PIVOT_SESSION"                     , "${ENCRYPT_IV_PIVOT_SESSION}"); // вектор шифрования для сущности session_key

// для всего остального - один
define("ENCRYPT_KEY_DEFAULT"			, "${ENCRYPT_KEY_DEFAULT}"); // ключ
define("ENCRYPT_IV_DEFAULT"			, "${ENCRYPT_IV_DEFAULT}");  // вектор шифрования

// -------------------------------------------------------
// SALT ДЛЯ УПАКОВЩИКОВ ПРОЕКТА
// -------------------------------------------------------

// соль для формирования session_map
define("SALT_PACK_SESSION"                , [
	1 => "${SALT_PACK_SESSION_V1}",
]);

// соль для формирования message_map
define("SALT_PACK_MESSAGE"                , [
	1 => "${SALT_PACK_MESSAGE_1}",
]);

// соль для формирования conversation_map
define("SALT_PACK_CONVERSATION"           , [
	1 => "${SALT_PACK_CONVERSATION_1}",
]);

// соль для формирования thread_map
define("SALT_PACK_THREAD"			, [
	1 => "${SALT_PACK_THREAD_1}",
]);

// соль для формирования file_map
define("SALT_PACK_FILE"				, [
	1 => "${SALT_PACK_FILE_1}",
	2 => "${SALT_PACK_FILE_2}",
	3 => "${SALT_PACK_FILE_3}",
]);

// соль для формирования invite_map
define("SALT_PACK_INVITE"			, [
	1 => "${SALT_PACK_INVITE_1}",
]);

// соль для формирования preview_map
define("SALT_PACK_PREVIEW"			, [
	1 => "${SALT_PACK_PREVIEW_1}",
]);

// соль для формирования call_map
define("SALT_PACK_CALL"                   , [
	1 => "${SALT_PACK_CALL_1}",
]);

// соль для формирования company_session
define("SALT_PACK_COMPANY_SESSION"				, [
	1 => "${SALT_PACK_COMPANY_SESSION_V1}",
]);

// -------------------------------------------------------
// КЛЮЧИ SOCKET ДЛЯ ОБЩЕНИЯ ВНУТРИ ПРИЛОЖЕНИЯ
// -------------------------------------------------------

// входные точки проектов, с которыми происходит общение по сокету
define("ENTRYPOINT_DOMINO"					, "${ENTRYPOINT_DOMINO}");
define("ENTRYPOINT_PIVOT"					, "${ENTRYPOINT_PIVOT}");
define("ENTRYPOINT_ANNOUNCEMENT"				, "${ENTRYPOINT_ANNOUNCEMENT}");
define("ENTRYPOINT_USERBOT"					, "${ENTRYPOINT_USERBOT}");
define("ENTRYPOINT_INTERCOM"					, "${ENTRYPOINT_INTERCOM}");

// ключи для сокет запросов с пивотом
define("COMPANY_TO_PIVOT_PRIVATE_KEY"			, "${COMPANY_TO_PIVOT_PRIVATE_KEY}");
define("PIVOT_TO_COMPANY_PUBLIC_KEY"			, "${PIVOT_TO_COMPANY_PUBLIC_KEY}");

define("SOCKET_KEY_CONVERSATION"				, "${SOCKET_KEY_CONVERSATION}");
define("SOCKET_KEY_THREAD"					, "${SOCKET_KEY_THREAD}");
define("SOCKET_KEY_COMPANY"					, "${SOCKET_KEY_COMPANY}");
define("SOCKET_KEY_ADMIN"					, "${SOCKET_KEY_ADMIN}");
define("SOCKET_KEY_TEST"					, "${SOCKET_KEY_TEST}");
define("SOCKET_KEY_SPEAKER"					, "${SOCKET_KEY_SPEAKER}");
define("SOCKET_KEY_FILE_BALANCER"				, "${SOCKET_KEY_FILE_BALANCER}");
define("SOCKET_KEY_PARTNER"          			, "${SOCKET_KEY_PARTNER}");
define("SOCKET_KEY_INTERCOM"					, "${SOCKET_KEY_INTERCOM}");
define("SOCKET_KEY_GO_COMPANY"          			, "${SOCKET_KEY_GO_COMPANY}");
define("SOCKET_KEY_GO_RATING"          			, "${SOCKET_KEY_GO_RATING}");
define("SOCKET_KEY_GO_EVENT"					, "${SOCKET_KEY_GO_EVENT}");
define("SOCKET_KEY_SENDER"          			, "${SOCKET_KEY_GO_SENDER}");
define("SOCKET_KEY_PIVOT"          			      , "${SOCKET_KEY_PIVOT}");
define("SOCKET_KEY_CRM"		      	, "${SOCKET_KEY_CRM}");
define("SOCKET_KEY_FILE_NODE"             , "${SOCKET_KEY_FILE_NODE}");
define("SOCKET_KEY_WWW"						, "${SOCKET_KEY_WWW}");

define("GLOBAL_ANNOUNCEMENT_PRIVATE_KEY"	, "${GLOBAL_ANNOUNCEMENT_PRIVATE_KEY}");
define("COMPANY_ANNOUNCEMENT_PRIVATE_KEY"	, "${COMPANY_ANNOUNCEMENT_PRIVATE_KEY}");

define("CDN_URL"                          , "${CDN_URL}");

// -------------------------------------------------------
// ОСТАЛЬНОЕ
// -------------------------------------------------------

// соль инициирующего токена для анонсов
define("SALT_INITIAL_ANNOUNCEMENT_TOKEN"              , "${SALT_INITIAL_ANNOUNCEMENT_TOKEN}");

// соль для формирования talking_hash, который необходим для подписи typing запросов к go_sender
define("SALT_TALKING_HASH"			, "${SALT_TALKING_HASH}");

// соль для action users
define("SALT_ACTION_USERS"			, "${SALT_ACTION_USERS}");

// ключ для хранения в shared_memory
define("SHM_KEY"					, 1876883780);

// ключ go_event сервиса
define("AUTH_BOT_USER_ID"			, "${AUTH_BOT_USER_ID}");
define("REMIND_BOT_USER_ID"			, "${REMIND_BOT_USER_ID}");
define("SUPPORT_BOT_USER_ID"			, "${SUPPORT_BOT_USER_ID}");

// нужно ли создавать диалоги поддержки всем или только тем, кто находится в массиве NEED_CREATE_SUPPORT_CONVERSATION_COMPANY_ID_LIST
define("IS_PUBLIC_USER_SUPPORT"							, ${IS_PUBLIC_USER_SUPPORT});

// список id компаний, в которых должен создаваться диалог
// чтобы не создавались диалоги при создании/вступлении в компанию до момента пока клиенты не будут готовы
define("NEED_CREATE_SUPPORT_CONVERSATION_COMPANY_ID_LIST"			, ${NEED_CREATE_SUPPORT_CONVERSATION_COMPANY_ID_LIST});

// true если нужно автоматически создавать диалог в интеркоме при создании пространства
define("IS_NEED_CREATE_CONVERSATION_IN_INTERCOM_ON_COMPANY_CREATE"	, ${IS_NEED_CREATE_CONVERSATION_IN_INTERCOM_ON_COMPANY_CREATE});

// соль для формирования talking_hash, который необходим для подписи typing запросов к go_sender
define("SALT_SENDER_HASH"				, "${SALT_SENDER_HASH}");

define("IS_URL_PREVIEW_ENABLED"           	, "${IS_IMAGE_URL_PREVIEW_ENABLED}");      // включен ли парсинг картинок по прямой ссылке
define("IS_IMAGE_URL_PREVIEW_ENABLED"     	, "${IS_IMAGE_URL_PREVIEW_ENABLED}");      // включен ли парсинг картинок по прямой ссылке
define("IS_VIDEO_URL_PREVIEW_ENABLED"     	, "${IS_VIDEO_URL_PREVIEW_ENABLED}");      // включен ли парсинг видео по прямой ссылке
define("IS_WS_USERS_FOR_GO_ENABLED"			, "${IS_WS_USERS_FOR_GO_ENABLED}");	       // нужно ли отправлять в go_reaction поле ws_users
define("IS_HIRING_SYSTEM_MESSAGES_ENABLED"	, "${IS_HIRING_SYSTEM_MESSAGES_ENABLED}"); // включены ли системные сообщения в чате найма

define("SKIP_PREVIEW_SENDER_LIST"			, [${SKIP_PREVIEW_SENDER_LIST}]);	// список юзеров, ссылки от которых не нужно парсить

// ключ для общения с пользовательским ботом
define("GLOBAL_USERBOT_PRIVATE_KEY"		            , "${GLOBAL_USERBOT_PRIVATE_KEY}");
define("COMPANY_USERBOT_PRIVATE_KEY"		, "${COMPANY_USERBOT_PRIVATE_KEY}");

// глобально отключенные группы событий для логирования
define("GLOBAL_DISABLED_ANALYTICS_EVENT_GROUP_LIST", ${GLOBAL_DISABLED_ANALYTICS_EVENT_GROUP_LIST});

// для работы с гибернацией компаний
define("NEED_COMPANY_HIBERNATE"				, ${NEED_COMPANY_HIBERNATE});
define("COMPANY_HIBERNATION_DELAYED_TIME"			, ${COMPANY_HIBERNATION_DELAYED_TIME});

// список пользователей, которым нужно слать ws с временем ответа
define("NEED_SEND_ANSWER_TIME_WS_USER_ID_LIST"		, ${NEED_SEND_ANSWER_TIME_WS_USER_ID_LIST});

// нужно ли создавать чат спасибо
define("IS_NEED_CREATE_RESPECT_CONVERSATION"		, ${IS_NEED_CREATE_RESPECT_CONVERSATION});

// время в секундах, сколько добавляем экранного времени пользователю
define("USER_SCREEN_TIME_SECONDS"			, ${USER_SCREEN_TIME_SECONDS});

// версии, для которых инкрементим метку updated_version
define("ANDROID_VERSION_WITH_INCREMENT_UPDATED_VERSION"	, ${ANDROID_VERSION_WITH_INCREMENT_UPDATED_VERSION});

// нужно ли слать сообщение в тред к требовательности
define("IS_NEED_SEND_EXACTNESS_COUNT_THREAD_MESSAGE"		, ${IS_NEED_SEND_EXACTNESS_COUNT_THREAD_MESSAGE});
// @formatter:on