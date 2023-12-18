<?php

namespace Compass\Company;

/**
 * Событие — создание заявки
 *
 * @event_category dismissal_request
 * @event_name     created
 */
class Type_Event_DismissalRequest_Created {

	/** @var string тип события */
	public const EVENT_TYPE = "dismissal_request.created";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(array $dismissal_request, string $user_comment, string $user_platform):Struct_Event_Base {

		$event_data = Struct_Event_DismissalRequest_Created::build($dismissal_request, $user_comment, $user_platform);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_DismissalRequest_Created {

		return Struct_Event_DismissalRequest_Created::build(...$event["event_data"]);
	}
}
