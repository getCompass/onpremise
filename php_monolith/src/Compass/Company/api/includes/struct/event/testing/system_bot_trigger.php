<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Триггер для системного бота.
 * Событие только для тестового сервера.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Testing_SystemBotTrigger extends Struct_Default {

	/** @var string тип завернутого события */
	public string $event_type;

	/** @var Struct_Event_Base тело события */
	public Struct_Event_Base $event_body;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(string $event_type, Struct_Event_Base $event_body):static {

		return new static([
			"event_type" => $event_type,
			"event_body" => $event_body,
		]);
	}
}
