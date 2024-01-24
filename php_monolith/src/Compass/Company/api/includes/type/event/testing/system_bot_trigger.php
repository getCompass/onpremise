<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Событие «Запрошено текстовое сообщение от системного бота».
 *
 * @event_category testing
 * @event_name     system_bot_trigger
 */
class Type_Event_Testing_SystemBotTrigger {

	/** @var string тип события */
	public const EVENT_TYPE = "testing.system_bot_trigger";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(string $event_type, Struct_Event_Base $event_body):Struct_Event_Base {

		$event_data = Struct_Event_Testing_SystemBotTrigger::build($event_type, $event_body);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}
}
