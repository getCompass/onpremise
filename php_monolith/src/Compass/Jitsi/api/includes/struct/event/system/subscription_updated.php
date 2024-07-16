<?php declare(strict_types = 1);

namespace Compass\Jitsi;

/**
 * Class Struct_Event_Base
 * Базовая структура события.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_System_SubscriptionUpdated extends Struct_Event_Default {

	public string $subscriber;

	public array $subscription_list;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(string $subscriber, array $subscription_list):static {

		return new static([
			"subscriber"        => $subscriber,
			"subscription_list" => $subscription_list,
		]);
	}
}
