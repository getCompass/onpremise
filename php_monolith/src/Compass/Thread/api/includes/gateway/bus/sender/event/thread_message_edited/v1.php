<?php

namespace Compass\Thread;

/**
 * класс описывающий событие action.thread_message_edited версии 1
 */
class Gateway_Bus_Sender_Event_ThreadMessageEdited_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "action.thread_message_edited";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"message_map"                 => \Entity_Validator_Structure::TYPE_STRING,
		"new_text"                    => \Entity_Validator_Structure::TYPE_STRING,
		"last_message_text_edited_at" => \Entity_Validator_Structure::TYPE_INT,
		"mention_user_id_list"        => \Entity_Validator_Structure::TYPE_ARRAY,
		"diff_mentioned_user_id_list" => \Entity_Validator_Structure::TYPE_ARRAY,
		"message"                     => \Entity_Validator_Structure::TYPE_OBJECT,
		"follower_list"               => \Entity_Validator_Structure::TYPE_ARRAY,
		"location_type"               => \Entity_Validator_Structure::TYPE_STRING,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(string $message_map,
						   string $new_text,
						   int $last_message_text_edited_at,
						   array $mention_user_id_list,
						   array $diff_mentioned_user_id_list,
						   array $message,
						   array $follower_list,
						   string $location_type):Struct_Sender_Event {

		return self::_buildEvent([
			"message_map"                 => (string) $message_map,
			"new_text"                    => (string) $new_text,
			"last_message_text_edited_at" => (int) $last_message_text_edited_at,
			"mention_user_id_list"        => (array) $mention_user_id_list,
			"diff_mentioned_user_id_list" => (array) $diff_mentioned_user_id_list,
			"message"                     => (object) $message,
			"follower_list"               => (array) $follower_list,
			"location_type"               => (string) $location_type,
		]);
	}
}