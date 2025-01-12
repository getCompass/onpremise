<?php

namespace Compass\FileBalancer;

// @formatter:off
// ----
// private/custom.php
// приватные константы, ключи шифрования/доступа к сервисам
// ----

// @formatter:on

define(__NAMESPACE__ . "\COMPANY_START_PORT" 	, "${COMPANY_START_PORT}");
define(__NAMESPACE__ . "\MOD_VALUE_FOR_PORT"	, "${MOD_VALUE_FOR_PORT}");

// включен ли режим доступа к файлам только для авторизованных пользователей
define(__NAMESPACE__ . "\IS_FILE_AUTH_RESTRICTION_ENABLED", ${IS_FILE_AUTH_RESTRICTION_ENABLED});