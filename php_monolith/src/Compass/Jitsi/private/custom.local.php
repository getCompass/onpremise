<?php

namespace Compass\Jitsi;

// endpoint для отправки уведомлений в Compass
define(__NAMESPACE__ . "\COMPASS_NOTICE_ENDPOINT"                , "${COMPASS_NOTICE_ENDPOINT}");

// расширенный ключ (32 символа)
define(__NAMESPACE__ . "\EXTENDED_ENCRYPT_KEY_DEFAULT"           , "${EXTENDED_ENCRYPT_KEY_DEFAULT}"); // ключ
define(__NAMESPACE__ . "\EXTENDED_ENCRYPT_IV_DEFAULT"            , "${EXTENDED_ENCRYPT_IV_DEFAULT}"); // вектор шифрования

// это домен, его можно использовать только как домен,
// например, для сессий, при формировании ссылок всегда
// нужно учитывать, что для приложения может быть указан
// дополнительный путь после доменного имени
define(__NAMESPACE__ . "\DOMAIN_JITSI"					, "${JITSI_DOMAIN}");

// константа которая указывает на путь к куки
define(__NAMESPACE__ . "\SESSION_COOKIE_DOMAIN"				, "${PIVOT_DOMAIN}");
define(__NAMESPACE__ . "\SESSION_WEB_COOKIE_DOMAIN"			, "${JITSI_DOMAIN}");

// -------------------------------------------------------
// SOCKET КЛЮЧИ ДЛЯ ДОВЕРЕННОГО ОБЩЕНИЯ МЕЖДУ МОДУЛЯМИ
// -------------------------------------------------------

define(__NAMESPACE__ . "\SOCKET_KEY_JITSI"                       , "${SOCKET_KEY_JITSI}");

// -------------------------------------------------------
// ОСТАЛЬНОЕ
// -------------------------------------------------------

define(__NAMESPACE__ . "\JITSI_FRONTEND_URL"                      , "${JITSI_FRONTEND_URL}");

// значение с которого начинается инкрементальный ID конференции
define(__NAMESPACE__ . "\BEGIN_INCREMENTAL_CONFERENCE_ID"		, ${BEGIN_INCREMENTAL_CONFERENCE_ID});