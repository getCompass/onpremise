<?php

namespace Compass\Conversation;

/**
 * Событие — отправляем системное сообщение рейтинга от бота пользователю
 *
 * @event_category message
 * @event_name     send_system_rating_message_to_user
 */
class Type_Event_Message_SendSystemRatingMessageToUser {

	/** @var string тип события */
	public const EVENT_TYPE = "message.send_system_rating_message_to_user";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(int $bot_user_id, int $receiver_user_id, int $year, int $week, int $count, string $name):Struct_Event_Base {

		$event_data = Struct_Event_Message_SendSystemRatingMessageToUser::build($bot_user_id, $receiver_user_id, $year, $week, $count, $name);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Message_SendSystemRatingMessageToUser {

		return Struct_Event_Message_SendSystemRatingMessageToUser::build(...$event["event_data"]);
	}
}
