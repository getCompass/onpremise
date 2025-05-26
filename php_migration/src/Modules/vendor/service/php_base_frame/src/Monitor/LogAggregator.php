<?php

namespace BaseFrame\Monitor;

/**
 * Класс для ведение логирования в рамках мониторинга.
 */
class LogAggregator {

	public const LOG_LEVEL_ERROR   = LogLine::LOG_LEVEL_ERROR;
	public const LOG_LEVEL_WARNING = LogLine::LOG_LEVEL_WARNING;
	public const LOG_LEVEL_INFO    = LogLine::LOG_LEVEL_INFO;

	/** @var self экземпляр синглтона */
	protected static self $_instance;

	/** @var bool флаг, определяющий работу агрегатора */
	protected bool $_is_enabled = false;

	/** @var bool флаг наличия экземпляра */
	protected static bool $_has_instance = false;

	/** @var int уровень логирования */
	protected int $_log_level = LogLine::LOG_LEVEL_ERROR;

	/** @var LogLine[] список элементов лога */
	protected array $_log_line_list = [];

	/** @var string[] список меток по умолчанию */
	protected array $_default_label_list = [];

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
	 * Устанавливает набор меток по умолчанию.
	 */
	public function setDefaultLabel(string $label_name, string|int|float $value):static {

		$this->_default_label_list[$label_name] = $value;
		return $this;
	}

	/**
	 * Устанавливает набор меток по умолчанию.
	 */
	public function setLogLevel(int $level):static {

		$this->_log_level = $level;
		return $this;
	}

	/**
	 * Добавляет запись лога.
	 */
	public function log(string $message, int $log_level = LogLine::LOG_LEVEL_INFO):LogLine {

		$log_line = new LogLine($message);

		// если уровень логирования поддерживается, то добавляем лог в хранилище
		// если не поддерживается, то лог не сохраняем, но все равно возвращаем,
		// чтобы не делать лишних проверок в вызывающих функциях
		if ($this->_is_enabled && $this->_log_level >= $log_level) {
			$this->_log_line_list[] = $log_line;
		}

		// добавляем дефолтные метки
		foreach ($this->_default_label_list as $label => $value) {
			$log_line->label($label, $value);
		}

		// сразу добавляем уровень записи лога исходя из переданного
		$log_line->label("level", $log_level);
		return $log_line;
	}

	/**
	 * Нужно ли сбросить данные в агрегатор.
	 */
	public function needFlush():bool {

		if (!$this->_is_enabled) {
			return false;
		}

		foreach ($this->_log_line_list as $trace) {

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
	public function flush():array {

		// конвертируем метрики в правильные массивы
		$to_flush = array_filter($this->_log_line_list, static fn(LogLine $el) => $el->isSealed());
		$output   = array_map(static fn(LogLine $el) => $el->prepare(), $to_flush);

		$this->_log_line_list = [];
		return $output;
	}
}
