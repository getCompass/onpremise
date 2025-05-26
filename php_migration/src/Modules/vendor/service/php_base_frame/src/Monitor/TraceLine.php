<?php

namespace BaseFrame\Monitor;

use JetBrains\PhpStorm\ArrayShape;

/**
 * Объект записи trace-лога.
 */
class TraceLine {

	protected string $_message;         // главное сообщение trace-лога
	protected bool   $_is_success;      // флаг успешности для trace-лога
	protected array  $_label_list = []; // список меток trace-лога

	protected float $_start_time;
	protected float $_done_time;

	protected bool  $_is_sealed = false; // завершен ли сбор метрики

	/**
	 * Constructor.
	 */
	public function __construct(string $message) {

		$this->_start_time = timeNs();
		$this->_message    = $message;
	}

	/**
	 * Добавляет метку к записи лога.
	 */
	public function label(string $label_name, string|int|float $value):static {

		$this->_label_list[$label_name] = (string) $value;
		return $this;
	}

	/**
	 * Фиксирует время исполнения для trace-span.
	 */
	public function seal(bool $is_success = true):void {

		$this->_is_sealed  = true;
		$this->_is_success = $is_success;
		$this->_done_time  = timeNs();
	}

	/**
	 * Проверяем, может ли элемент быть собран для передачи дальше.
	 */
	public function isSealed():bool {

		return $this->_is_sealed;
	}

	/**
	 * Конвертирует объект массив с правильным именованием полей.
	 */
	#[ArrayShape(["start_at" => "mixed", "done_at" => "int", "log" => "string", "is_success" => "bool", "label_list" => "array"])]
	public function prepare():array {

		return [
			"start_at"   => (int) $this->_start_time,
			"done_at"    => (int) $this->_done_time,
			"log"        => $this->_message,
			"is_success" => $this->_is_success,
			"label_list" => $this->_label_list,
		];
	}
}
