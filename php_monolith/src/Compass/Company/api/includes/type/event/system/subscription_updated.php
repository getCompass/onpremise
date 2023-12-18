<?php

namespace Compass\Company;

/**
 * Событие — подписки обновлены.
 *
 * @event_category system_event_broker
 * @event_name     subscription_updated
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
}
