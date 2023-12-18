<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Структура события «проверка последней активности».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Company_CheckLastActivity extends Struct_Default {

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build():static {

		return new static([]);
	}
}
