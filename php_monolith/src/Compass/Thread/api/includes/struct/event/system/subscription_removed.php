<?php

declare(strict_types = 1);

namespace Compass\Thread;

/**
 * Class Struct_Event_Base
 * Базовая структура события.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_System_SubscriptionRemoved extends Struct_Default {

	public string $subscriber;

	public Struct_Event_System_SubscriptionItem $subscription_item;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(string $subscriber, Struct_Event_System_SubscriptionItem $subscription_item):static {

		return new static([
			"subscriber"        => $subscriber,
			"subscription_item" => $subscription_item,
		]);
	}
}
