<?php

namespace Compass\Thread;

/**
 * Событие — пользователь присоединился к компании.
 *
 * @event_category user_company
 * @event_name user_joined
 */
class Type_Event_System_SubscriptionUpdated {

	/** @var string тип события */
	public const EVENT_TYPE = "system_event_broker.subscription_updated";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(string $subscriber, array $subscription_list):Struct_Event_Base {

		$event_data = Struct_Event_System_SubscriptionUpdated::build($subscriber, $subscription_list);

		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_System_SubscriptionUpdated {

		return Struct_Event_System_SubscriptionUpdated::build(...$event["event_data"]);
	}
}
