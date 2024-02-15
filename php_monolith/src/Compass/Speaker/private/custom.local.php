<?php

namespace Compass\Speaker;

// @formatter:off
// ----
// private/custom.php
// приватные константы, ключи шифрования/доступа к сервисам
// ----

// -------------------------------------------------------
// JANUS
// -------------------------------------------------------

// значение ширины канала от которой отталкиваемся в необходимости изменения bitrate комнаты
define(__NAMESPACE__ . "\JANUS_CLIENT_OPTIMAL_BANDWIDTH"	, ${JANUS_CLIENT_OPTIMAL_BANDWIDTH_MB} * 1024 * 1000);

// список допустимых значений bitrate для разговорной комнаты
define(__NAMESPACE__ . "\JANUS_ROOM_BITRATE_LIST"		, ${JANUS_ROOM_BITRATE_LIST});

// секрет для получения пользовательского токена
define(__NAMESPACE__ . "\JANUS_USER_TOKEN_SECRET"			, "${JANUS_USER_TOKEN_SECRET}");

// -------------------------------------------------------
// OTHER
// -------------------------------------------------------

// флаг, включен ли сбор аналитики по соединениям
define(__NAMESPACE__ . "\ANALYTICS_IS_ENABLED"			, "${ANALYTICS_IS_ENABLED}");

// флаг, включена ли сортировка событий janus по приоритетности
define(__NAMESPACE__ . "\SORT_EVENTS_BY_IMPORTANCE_IS_ENABLED"	, "${SORT_EVENTS_BY_IMPORTANCE_IS_ENABLED}");

// флаг, включен ли сбор трасировки сети до участников звонка
define(__NAMESPACE__ . "\IS_USER_NETWORK_TRACEROUTE_ENABLED"	, "${IS_USER_NETWORK_TRACEROUTE_ENABLED}");

// глобально отключенные группы событий для логирования
define(__NAMESPACE__ . "\GLOBAL_DISABLED_ANALYTICS_EVENT_GROUP_LIST", ${GLOBAL_DISABLED_ANALYTICS_EVENT_GROUP_LIST});

// @formatter:on
