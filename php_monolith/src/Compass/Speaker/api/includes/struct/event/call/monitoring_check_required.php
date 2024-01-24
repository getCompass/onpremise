<?php

declare(strict_types = 1);

namespace Compass\Speaker;

/**
 * Class Struct_Event_Base
 * Базовая структура события.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Call_MonitoringCheckRequired extends Struct_Default {

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build():static {

		return new static([]);
	}
}
