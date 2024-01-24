<?php

// @formatter:off
// ----
// private/custom.php
// приватные константы, ключи шифрования/доступа к сервисам
// ----

// -------------------------------------------------------
// JANUS
// -------------------------------------------------------

// значение ширины канала от которой отталкиваемся в необходимости изменения bitrate комнаты
define("JANUS_CLIENT_OPTIMAL_BANDWIDTH"	, ${JANUS_CLIENT_OPTIMAL_BANDWIDTH_MB} * 1024 * 1000);

// список допустимых значений bitrate для разговорной комнаты
define("JANUS_ROOM_BITRATE_LIST"		, ${JANUS_ROOM_BITRATE_LIST});

// секрет для получения пользовательского токена
define("JANUS_USER_TOKEN_SECRET"			, "${JANUS_USER_TOKEN_SECRET}");

// -------------------------------------------------------
// OTHER
// -------------------------------------------------------

// флаг, включен ли сбор аналитики по соединениям
define("ANALYTICS_IS_ENABLED"			, "${ANALYTICS_IS_ENABLED}");

// флаг, включена ли сортировка событий janus по приоритетности
define("SORT_EVENTS_BY_IMPORTANCE_IS_ENABLED"	, "${SORT_EVENTS_BY_IMPORTANCE_IS_ENABLED}");

// флаг, включен ли сбор трасировки сети до участников звонка
define("IS_USER_NETWORK_TRACEROUTE_ENABLED"	, "${IS_USER_NETWORK_TRACEROUTE_ENABLED}");

// @formatter:on
