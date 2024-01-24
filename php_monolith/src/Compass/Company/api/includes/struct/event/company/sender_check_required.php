<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Class Struct_Event_Base
 * Базовая структура события.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Company_SenderCheckRequired extends Struct_Default {

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build():static {

		return new static([]);
	}
}
