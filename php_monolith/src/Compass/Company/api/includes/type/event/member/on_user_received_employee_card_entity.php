<?php

namespace Compass\Company;

/**
 * Событие — пользователь получил сущность карточки (спасибо/требовательность/достижение).
 *
 * @event_category member
 * @event_name     on_user_received_employee_card_entity
 */
class Type_Event_Member_OnUserReceivedEmployeeCardEntity {

	/** @var string тип события */
	public const EVENT_TYPE = "member.on_user_received_employee_card_entity";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function create(string $entity_type, string $message_map, int $sender_user_id, int $received_user_id, int $week_count, int $month_count):Struct_Event_Base {

		$event_data = Struct_Event_Member_OnUserReceivedEmployeeCardEntity::build($entity_type, $message_map, $sender_user_id, $received_user_id, $week_count, $month_count);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 */
	public static function parse(array $event):Struct_Event_Member_OnUserReceivedEmployeeCardEntity {

		return Struct_Event_Member_OnUserReceivedEmployeeCardEntity::build(...$event["event_data"]);
	}
}
