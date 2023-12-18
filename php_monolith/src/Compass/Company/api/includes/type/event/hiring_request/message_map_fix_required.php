<?php

namespace Compass\Company;

/**
 * Событие — необходимо обновить мапы сообщений у заявок.
 *
 * @event_category hiring_request
 * @event_name     message_map_fix_required
 */
class Type_Event_HiringRequest_MessageMapFixRequired {

	/** @var string тип события */
	public const EVENT_TYPE = "hiring_request.message_map_fix_required";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(array $hiring_request_id_list, int $date_from):Struct_Event_Base {

		$event_data = Struct_Event_HiringRequest_MessageMapFixRequired::build($hiring_request_id_list, $date_from);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_HiringRequest_MessageMapFixRequired {

		return Struct_Event_HiringRequest_MessageMapFixRequired::build(...$event["event_data"]);
	}
}
