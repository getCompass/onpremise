<?php

namespace Compass\Conversation;

// @formatter:off

// временная штука для инвайтов
// дополнять полями invitation до совместимости с invite
define(__NAMESPACE__ . "\FILL_INVITATION_TO_INVITE_LEVEL"  		, "${FILL_INVITATION_TO_INVITE_LEVEL}");

// соль для подписи пользователей доступных для приглашения
define(__NAMESPACE__ . "\SALT_ALLOWED_USERS_FOR_INVITE"		, "${SALT_ALLOWED_USERS_FOR_INVITE}");

// флаг отправлять ли сообщение об успешной авторизации устройства
define(__NAMESPACE__ . "\IS_ALLOW_SEND_DEVICE_LOGIN_SUCCESS"	, ${IS_ALLOW_SEND_DEVICE_LOGIN_SUCCESS});
