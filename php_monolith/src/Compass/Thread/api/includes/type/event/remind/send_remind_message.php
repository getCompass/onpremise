<?php

namespace Compass\Thread;

/**
 * Событие — отправить сообщение-Напоминание
 *
 * @event_category remind
 * @event_name     send_remind_message
 */
class Type_Event_Remind_SendRemindMessage {

	/** @var string тип события */
	public const EVENT_TYPE = "remind.send_remind_message";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(int $remind_id):Struct_Event_Base {

		$event_data = Struct_Event_Remind_SendRemindMessage::build($remind_id);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 */
	public static function parse(array $event):Struct_Event_Remind_SendRemindMessage {

		return Struct_Event_Remind_SendRemindMessage::build(...$event["event_data"]);
	}
}
