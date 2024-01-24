<?php

declare(strict_types = 1);

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Структура события когда компания пробудилась
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Company_OnWakeUp extends Struct_Default {

	/**
	 * Статический конструктор.
	 *
	 * @throws ParseFatalException
	 */
	public static function build():static {

		return new static([]);
	}
}
