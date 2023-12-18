<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Базовая структура события «изменен статус заявки».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_HiringRequest_StatusChanged extends Struct_Default {

	/** @var array сущность заявки */
	public array $hiring_request;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(array $hiring_request):static {

		return new static([
			"hiring_request" => $hiring_request,
		]);
	}
}