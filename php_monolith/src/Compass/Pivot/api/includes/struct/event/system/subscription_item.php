<?php declare(strict_types = 1);

namespace Compass\Pivot;

/**
 * Class Struct_Event_Base
 * Базовая структура события.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_System_SubscriptionItem extends Struct_Event_Default {

	/** @var int $trigger_type тип адреса. Описывает как будут приходить события из go_event в модуль */
	public int $trigger_type;

	/** @var string $event событие */
	public string $event;

	/** @var array экстра для подписки */
	public array $extra;

	/**
	 * Статический конструктор.
	 *
	 * @param int    $trigger_type
	 * @param string $event
	 * @param array  $extra
	 *
	 * @return static
	 * @throws \parseException
	 */
	public static function build(int $trigger_type, string $event, array $extra):static {

		return new static([
			"trigger_type" => $trigger_type,
			"event"        => $event,
			"extra"        => $extra,
		]);
	}
}
