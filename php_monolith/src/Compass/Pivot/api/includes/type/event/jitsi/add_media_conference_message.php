<?php

namespace Compass\Pivot;

/**
 * Событие — отправить сообщение с данными о конференции в команду
 *
 * @event_category partner
 * @event_name     send_task
 */
class Type_Event_Jitsi_AddMediaConferenceMessage {

	/** @var string тип события */
	public const EVENT_TYPE = "jitsi.add_media_conference_message";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему
	 *
	 * @throws \parseException
	 */
	public static function create(string $space_id, string $user_id, string $conversation_map, string $conference_id, string $accept_status, string $link, string $conference_code):Struct_Event_Base {

		$event_data = Struct_Event_Jitsi_AddMediaConferenceMessage::build($space_id, $user_id, $conversation_map, $conference_id, $accept_status, $link, $conference_code);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных
	 *
	 */
	public static function parse(array $event):Struct_Event_Jitsi_AddMediaConferenceMessage {

		return Struct_Event_Jitsi_AddMediaConferenceMessage::build(...$event["event_data"]);
	}
}