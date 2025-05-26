<?php declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Событие — необходимо удалить просроченные записи прочитавших участников
 *
 */
class Type_Event_Conversation_ClearExpiredMessageReadParticipants {

	/** @var string тип события */
	public const EVENT_TYPE = "conversation.clear_expired_message_read_participants";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create():Struct_Event_Base {

		$event_data = Struct_Event_Conversation_ClearExpiredMessageReadParticipants::build();
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 * @noinspection PhpUnusedParameterInspection
	 */
	public static function parse(array $event):Struct_Event_Conversation_ClearExpiredMessageReadParticipants {

		return Struct_Event_Conversation_ClearExpiredMessageReadParticipants::build();
	}
}
