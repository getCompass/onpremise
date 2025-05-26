<?php

namespace BaseFrame\Monitor;

use BaseFrame\Exception\Domain\ReturnFatalException;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Класс для работы с trace-логами.
 */
class TraceAggregator {

	/** @var self экземпляр синглтона */
	protected static self $_instance;

	/** @var bool флаг наличия экземпляра */
	protected static bool $_has_instance = false;

	/** @var bool флаг, определяющий работу сбора trace-логов */
	protected bool $_is_enabled = false;

	/** @var bool флаг, определяющий является ли текущий span корневым для запроса */
	protected bool $_is_root;

	protected string $_request_id;  // идентификатор запроса, в рамках которого ведется сбор данных
	protected string $_unique_key;  // уникальный идентификатор trace-лога (для построение иерархии)
	protected string $_parent_name; // уникальное имя родителя trace-лога (для построение иерархии)
	protected string $_module;      // имя текущего модуля

	/** @var TraceLine[] список элементов лога */
	protected array $_trace_line_list = [];

	/**
	 * Закрываем конструктор.
	 */
	protected function __construct() {

	}

	/**
	 * Возвращает singleton-экземпляр для класса.
	 */
	public static function instance():static {

		if (static::$_has_instance === false) {

			static::$_instance     = new static();
			static::$_has_instance = true;
		}

		return static::$_instance;
	}

	/**
	 * Меняет флаг работы для агрегатора.
	 */
	public function set(bool $is_enabled):static {

		$this->_is_enabled = $is_enabled;
		return $this;
	}

	/**
	 * Функция, инициализирующая span, должна вызываться один раз при запуске.
	 * @return $this
	 * @throws ReturnFatalException
	 */
	public function init(string $request_id, string $module, bool $is_root = false, string $parent_name = ""):static {

		if ($is_root && $parent_name !== "") {
			throw new ReturnFatalException("root span must have no parent");
		}

		$this->_request_id  = $request_id;
		$this->_is_root     = $is_root;
		$this->_unique_key  = $this->_request_id . ":" . generateRandomString(8);
		$this->_parent_name = $parent_name;
		$this->_module      = $module;

		return $this;
	}

	/**
	 * Добавляет новый trace-лог в список
	 */
	public function trace(string $message):TraceLine {

		$trace_line = new TraceLine($message);

		if ($this->_is_enabled) {
			$this->_trace_line_list[] = $trace_line;
		}

		return $trace_line;
	}

	/**
	 * Нужно ли сбросить данные в агрегатор.
	 */
	public function needFlush():bool {

		if (!$this->_is_enabled) {
			return false;
		}

		foreach ($this->_trace_line_list as $trace) {

			if ($trace->isSealed()) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Возвращает готовые к пересылке данные и сбрасывает текущее хранилище trace-логов.
	 * @return array
	 */
	#[ArrayShape(["name" => "string", "module" => "string", "is_root" => "bool", "parent_name" => "string", "request_id" => "string", "trace_line_list" => "mixed"])]
	public function flush():array {

		$to_flush = array_filter($this->_trace_line_list, static fn(TraceLine $el) => $el->isSealed());

		$output = [
			"name"            => $this->_unique_key,
			"module"          => $this->_module,
			"is_root"         => $this->_is_root,
			"parent_name"     => $this->_parent_name,
			"request_id"      => $this->_request_id,
			"trace_line_list" => array_map(static fn(TraceLine $el) => $el->prepare(), $to_flush),
		];

		$this->_trace_line_list = [];
		return $output;
	}
}
