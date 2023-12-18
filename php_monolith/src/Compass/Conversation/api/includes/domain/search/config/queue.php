<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс для настройки очереди индексации.
 */
class Domain_Search_Config_Queue {

	/** @var string доступ только для указанного списка пространств */
	public const ALLOW_FAILED = "allow";

	/** @var string ограничений для поиска нет */
	public const DISALLOW_FAILED = "disallow";

	/**
	 * Список с названиями очередей, для которых существует конфиг
	 */
	public const ENTITY_REPARATION_QUEUE = "entity_preparation_queue";
	public const INDEX_FILLING_QUEUE     = "index_filling_queue";

	/**
	 * Проверяет, возможна ли работа поиска в указанном пространстве.
	 */
	public static function isFailAllowed(string $queue_name):bool {

		$config = static::_load($queue_name);

		if ($config["fail_rule"] === static::ALLOW_FAILED) {
			return true;
		}

		if ($config["fail_rule"] === static::DISALLOW_FAILED) {
			return false;
		}

		throw new \BaseFrame\Exception\Domain\ReturnFatalException("bad search config approach");
	}

	/**
	 * Возвращает задержку перед следующей итерацией, если очередь пустая.
	 */
	public static function getNextIterationDelayOnEmptyQueue(string $queue_name):int {

		$config = static::_load($queue_name);
		return $config["next_iteration_delay_on_empty_queue"] ?? 15;
	}

	/**
	 * Возвращает задержку перед следующей итерацией, если есть еще задачи.
	 */
	public static function getNextIterationDelayOnFilledQueue(string $queue_name):int {

		$config = static::_load($queue_name);
		return $config["next_iteration_delay_on_filled_queue"] ?? 0;
	}

	/**
	 * Возвращает задержку перед следующей итерацией, если обработка завершилась ошибкой.
	 */
	public static function getNextIterationDelayOnFail(string $queue_name):int {

		$config = static::_load($queue_name);
		return $config["next_iteration_delay_on_fail"] ?? 60;
	}

	/**
	 * Возвращает лимит ОЗУ воркера для php.ini.
	 */
	public static function getIniMemoryLimit(string $queue_name):string {

		$config = static::_load($queue_name);
		return $config["ini_memory_limit"] ?? "2G";
	}

	/**
	 * Возвращает использованный процент памяти, при котором нужно остановить работу воркера.
	 */
	public static function getMemoryPercentLimit(string $queue_name):float {

		$config = static::_load($queue_name);
		return $config["memory_percent_limit"] ?? 0.75;
	}

	/**
	 * Возвращает время исполнения для единичного вызова воркера.
	 */
	public static function getExecutionTimeLimit(string $queue_name):int {

		$config = static::_load($queue_name);
		return $config["execution_time_limit"] ?? 60 * 5;
	}

	/**
	 * Загружает конфиг доступа к поиску.
	 */
	protected static function _load(string $queue_name):array {

		$config = getConfig("SEARCH");

		if (!isset($config[$queue_name])) {
			throw new ParseFatalException("unexpected queue_name: {$queue_name}");
		}

		return $config[$queue_name];
	}
}