<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Структура события, когда компания проснулась
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Company_OnWakeUp extends Struct_Default {

	/**
	 * Статический конструктор.
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function build():static {

		return new static([]);
	}
}
