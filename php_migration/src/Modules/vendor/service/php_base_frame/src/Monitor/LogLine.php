<?php

namespace BaseFrame\Monitor;

use JetBrains\PhpStorm\ArrayShape;

/**
 * Объект записи лога.
 *
 * Включает в себя как сообщение, так и метки,
 * позволяющие отследить источник лога в дальнейшем
 */
class LogLine {

	public const PRECISION_S  = "s";
	public const PRECISION_MS = "ms";
	public const PRECISION_US = "us";
	public const PRECISION_NS = "ns";

	public const LOG_LEVEL_ERROR   = 3;
	public const LOG_LEVEL_WARNING = 6;
	public const LOG_LEVEL_INFO    = 9;

	/** @var string универсальная метка времени работы */
	protected const _PROCESS_TIME = "process_time";

	protected string $_message;         // главное сообщение лога
	protected int    $_timestamp;       // временная метка лога
	protected array  $_label_list = []; // список меток лога

	protected bool $_is_sealed = false;

	/**
	 * Constructor.
	 */
	public function __construct(string $message) {

		$this->_message   = $message;
		$this->_timestamp = timeNs();
	}

	/**
	 * Добавляет метку к записи лога.
	 */
	public function label(string $label_name, string|int|float $value):static {

		$this->_label_list[$label_name] = (string) $value;
		return $this;
	}

	/**
	 * Запечатывает лог.
	 * Любой лог должен быть запечатан, иначе он не будет передан в сборщик.
	 */
	public function seal():void {

		$this->_is_sealed = true;
	}

	/**
	 * Проверяем, может ли элемент быть собран для передачи дальше.
	 */
	public function isSealed():bool {

		return $this->_is_sealed;
	}

	/**
	 * Фиксирует время с момента создания лога и добавляет ее как метку.
	 */
	public function since(string $precision = self::PRECISION_MS):static {

		$time = timeNs() - $this->_timestamp;

		if ($precision === self::PRECISION_US) {
			$time /= 1_000;
		}

		if ($precision === self::PRECISION_MS) {
			$time /= 1_000_000;
		}

		if ($precision === self::PRECISION_S) {
			$time /= 1_000_000_000;
		}

		$this->label(static::_PROCESS_TIME . "_$precision", $time);
		return $this;
	}

	/**
	 * Конвертирует объект в массив с правильным именованием полей.
	 */
	#[ArrayShape(["timestamp" => "mixed", "message" => "string", "label_list" => "array"])]
	public function prepare():array {

		return [
			"timestamp"  => $this->_timestamp,
			"message"    => $this->_message,
			"label_list" => $this->_label_list,
		];
	}
}
