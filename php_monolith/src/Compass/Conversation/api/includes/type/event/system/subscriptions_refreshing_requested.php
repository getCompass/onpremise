<?php

namespace Compass\Conversation;

/**
 * Событие — пользователь присоединился к компании.
 *
 * @event_category user_company
 * @event_name user_joined
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
