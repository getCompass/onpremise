<?php /** @noinspection DuplicatedCode */

declare(strict_types=1);

/**
 * Файл модуля.
 * По своей сути обертка для всех возможно шлюзов, используемых в модуле.
 *
 * Основная задача класса — обеспечивать единый доступ ко всем подключениям
 * и предоставлять возможность отключиться ото всюду разом.
 *
 * @package Compass\Janus
 */

namespace Compass\Janus;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс шардинга для изоляции настроек подключения внутри модуля.
 * @package Compass\Janus
 */
class ShardingGateway extends \ShardingGateway {

	protected static ?ShardingGateway $_instance = null;

	/**
	 * Инициализирует экземпляр работы с шардящимися подключениями.
	 * @throws ParseFatalException
	 */
	public static function instance():static {

		if (is_null(static::$_instance)) {

			static::$_instance = new ShardingGateway([

			]);
		}

		return static::$_instance;
	}
}
