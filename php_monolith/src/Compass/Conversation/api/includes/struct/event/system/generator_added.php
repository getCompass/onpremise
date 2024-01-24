<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Class Struct_Event_Base
 * Базовая структура события.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_System_GeneratorAdded extends Struct_Default {

	/** @var string ид пользователя */
	public string $name;

	/** @var int частота выполнения */
	public int $period;

	/** @var Struct_Event_System_SubscriptionItem подписчик */
	public Struct_Event_System_SubscriptionItem $subscription_item;

	/** @var array данные события которое будет выбрасываться */
	public array $event_data;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(string $name, int $period, Struct_Event_System_SubscriptionItem $subscription_item, array $event_data = []):static {

		return new static([
			"name"              => $name,
			"period"            => $period,
			"subscription_item" => $subscription_item,
			"event_data"        => (array) $event_data,
		]);
	}
}
