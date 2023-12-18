<?php

namespace Compass\Speaker;

/**
 * Событие — пользователь присоединился к компании.
 *
 * @event_category user_company
 * @event_name     user_joined
 */
class Type_Event_System_GeneratorAdded {

	/** @var string тип события */
	public const EVENT_TYPE = "system_event_broker.generator_added";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(string $name, int $period, Struct_Event_System_SubscriptionItem $subscription_item, array $event_data):Struct_Event_Base {

		$event_data = Struct_Event_System_GeneratorAdded::build($name, $period, $subscription_item, $event_data);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}
}
