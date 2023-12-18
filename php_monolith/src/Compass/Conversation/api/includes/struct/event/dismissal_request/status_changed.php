<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Базовая структура события «изменен статус заявки».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_DismissalRequest_StatusChanged extends Struct_Default {

	/** @var array сущность заявки */
	public array $dismissal_request;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(array $dismissal_request):static {

		return new static([
			"dismissal_request" => $dismissal_request,
		]);
	}
}
