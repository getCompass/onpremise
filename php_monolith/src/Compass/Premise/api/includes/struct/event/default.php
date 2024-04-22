<?php declare(strict_types = 1);

namespace Compass\Premise;

/**
 * Дефолтный родительский-класс для всех структур данных.
 * Включает в себя конструктор, принимающий массив с данными на вход и сеттер, который не дает определят левые поля
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Default {

	public string $unique_key = "";

	/**
	 * Struct_Default constructor.
	 *
	 * @throws
	 */
	public function __construct(array $param = []) {

		$caller_class_name = get_class($this);

		// собираем атрибуты класса
		$class_attributes = get_class_vars($caller_class_name);

		if (!isset($param["unique_key"])) {
			$param["unique_key"] = $this->unique_key ?? "";
		}

		// сравниваем количество переданных аргументов в параметрах и количество доступных атрибутов
		if (count($class_attributes) !== count($param)) {
			throw new \parseException("passed incorrect argument count for `$caller_class_name` class constructor");
		}

		foreach ($param as $k => $v) {

			// если в параметрах пришёл левак
			if (!property_exists($this, $k)) {
				throw new \parseException("unsupported field {$k} was given for  `$caller_class_name` class");
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

		throw new \parseException("struct is immutable data");
	}
}