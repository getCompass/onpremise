<?php

// @formatter:off
// ----
// private/custom.php
// приватные константы, ключи шифрования/доступа к сервисам
// ----

namespace Compass\Premise;

// константа которая указывает на путь к куки
define(__NAMESPACE__ . "\SESSION_COOKIE_DOMAIN"                        , "${PIVOT_DOMAIN}");

define(__NAMESPACE__ . "\SERVER_ACTIVATION_MESSAGE"		, "${SERVER_ACTIVATION_MESSAGE}");
define(__NAMESPACE__ . "\SERVER_UID"				, "${SERVER_UID}"); // uid сервера