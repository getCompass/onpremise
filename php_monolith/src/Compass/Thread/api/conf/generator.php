<?php

namespace Compass\Thread;

// конфиг с дефолтными генераторами событий
$CONFIG["GENERATOR"] = [


	/**
	 * Воркер для удаления старых прочитавших участников
	 */
	"clear_expired_thread_message_read_participants_worker" => [
		"period"            => DAY1,
		"subscription_item" => [
			"trigger_type" => 5,
			"event"        => Type_Event_Thread_ClearExpiredMessageReadParticipants::EVENT_TYPE,
			"extra"        => [
				"type"        => 2,
				"module"      => "php_thread",
				"group"       => Type_Attribute_EventListener::SLOW_GROUP,
				"error_limit" => 0
			],
		],
		"event_data"        => [],
	],
];

return $CONFIG;