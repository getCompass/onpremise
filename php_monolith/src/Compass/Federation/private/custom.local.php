<?php

namespace Compass\Federation;

// endpoint для отправки уведомлений в Compass
define(__NAMESPACE__ . "\COMPASS_NOTICE_ENDPOINT"                      , "${COMPASS_NOTICE_ENDPOINT}");

// расширенный ключ (32 символа)
define(__NAMESPACE__ . "\EXTENDED_ENCRYPT_KEY_DEFAULT"                 , "${EXTENDED_ENCRYPT_KEY_DEFAULT}"); // ключ
define(__NAMESPACE__ . "\EXTENDED_ENCRYPT_IV_DEFAULT"                  , "${EXTENDED_ENCRYPT_IV_DEFAULT}"); // вектор шифрования

// -------------------------------------------------------
// SOCKET КЛЮЧИ ДЛЯ ДОВЕРЕННОГО ОБЩЕНИЯ МЕЖДУ МОДУЛЯМИ
// -------------------------------------------------------

define(__NAMESPACE__ . "\SOCKET_KEY_FEDERATION"                        , "${SOCKET_KEY_FEDERATION}");