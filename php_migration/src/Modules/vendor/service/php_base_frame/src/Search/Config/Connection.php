<?php declare(strict_types=1);

namespace BaseFrame\Search\Config;

/**
 * Класс-конфигуратор подключения к Manticore Search.
 */
#[\JetBrains\PhpStorm\Immutable]
class Connection {

	/**
	 * Класс-конфигуратор подключения к Manticore Search.
	 */
	public function __construct(
		public string $host,
		public int    $port,
	) {

		// просто структура с данными
	}
}