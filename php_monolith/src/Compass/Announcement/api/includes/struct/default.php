<?php declare(strict_types = 1);

namespace Compass\Announcement;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Дефолтный родительский-класс для всех структур данных.
 * Включает в себя конструктор, принимающий массив с данными на вход и сеттер, который не дает определят левые поля
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Default {

	/**
	 * Сеттер, запрещает установку значений для полей.
	 *
	 * @param string $name
	 * @param mixed $value
	 *
	 * @throws
	 * @mixed
	 */
	public function __set(string $name, mixed $value):void {

		throw new ParseFatalException("struct is immutable data");
	}
}