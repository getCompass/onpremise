<?php

namespace Compass\Federation;

/**
 * Событие — запрошен список подписок.
 *
 * @event_category event_system
 * @event_name     subscriptions_refreshing_requested
 */
class Type_Event_System_SubscriptionsRefreshingRequested {

	/** @var string тип события */
	public const EVENT_TYPE = "system.subscriptions_refreshing_requested";

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_System_SubscriptionsRefreshingRequested {

		return Struct_Event_System_SubscriptionsRefreshingRequested::build();
	}
}
