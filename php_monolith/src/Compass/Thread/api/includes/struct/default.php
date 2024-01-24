<?php

declare(strict_types = 1);

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Дефолтный родительский-класс для всех структур данных.
 * Включает в себя конструктор, принимающий массив с данными на вход и сеттер, который не дает определят левые поля
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Default {

	/**
	 * Struct_Default constructor.
	 *
	 * @throws
	 */
	public function __construct(array $param = []) {

		$caller_class_name = get_class($this);

		if (count(get_class_vars($caller_class_name)) !== count($param)) {
			throw new ParseFatalException("passed incorrect argument count for `$caller_class_name` class constructor");
		}

		foreach ($param as $k => $v) {
			if (!property_exists($this, $k)) {

				throw new ParseFatalException("unsupported field {$k} was given for  `$caller_class_name` class");
			}

			$this->$k = $v;
		}
	}

	/**
	 * Сеттер, запрещает установку значений для полей.
	 *
	 * @throws
	 * @mixed
	 */
	public function __set(string $name, mixed $value):void {

		throw new ParseFatalException("struct is immutable data");
	}
}