<?php

namespace Compass\Federation;

/**
 * Событие — подписка удалена.
 *
 * @event_category system_event_broker
 * @event_name     subscription_removed
 */
class Type_Event_System_SubscriptionRemoved {

	/** @var string тип события */
	public const EVENT_TYPE = "system_event_broker.subscription_removed";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(string $subscriber, Struct_Event_System_SubscriptionItem $subscription):Struct_Event_Base {

		$event_data = Struct_Event_System_SubscriptionRemoved::build($subscriber, $subscription);

		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_System_SubscriptionRemoved {

		return Struct_Event_System_SubscriptionRemoved::build(...$event["event_data"]);
	}
}
