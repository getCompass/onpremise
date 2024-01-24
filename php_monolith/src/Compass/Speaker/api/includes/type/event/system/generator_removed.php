<?php

namespace Compass\Speaker;

/**
 * Событие — удаляем генератор
 *
 * @event_category system_event_broker
 * @event_name     generator_removed
 */
class Type_Event_System_GeneratorRemoved {

	/** @var string тип события */
	public const EVENT_TYPE = "system_event_broker.generator_removed";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(string $name, int $period, Struct_Event_System_SubscriptionItem $subscription_item, array $event_data):Struct_Event_Base {

		$event_data = Struct_Event_System_GeneratorRemoved::build($name, $period, $subscription_item, $event_data);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}
}
