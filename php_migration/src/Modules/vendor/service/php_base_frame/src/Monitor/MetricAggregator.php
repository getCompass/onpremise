<?php

namespace BaseFrame\Monitor;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс для работы с метриками.
 */
class MetricAggregator {

	/** @var self экземпляр синглтона */
	protected static self $_instance;

	/** @var bool флаг наличия экземпляра */
	protected static bool $_has_instance = false;

	/** @var bool флаг, определяющий работу сбора метрик */
	protected bool $_is_enabled = false;

	/** @var Metric[] список элементов лога */
	protected array $_metric_list = [];

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
	 * Добавляет новую метрику в список
	 * @throws ReturnFatalException
	 */
	public function metric(string $name, float $value, int $behaviour, array $label_list = []):Metric {

		$metric = new Metric($name, $value, $behaviour);

		if ($this->_is_enabled) {
			$this->_metric_list[] = $metric;
		}

		// добавляем дефолтные метки
		foreach ($this->_default_label_list as $label_name => $label_value) {
			$metric->label($label_name, $label_value);
		}

		// добавляем переданные метки
		foreach ($label_list as $label_name => $label_value) {
			$metric->label($label_name, $label_value);
		}

		return $metric;
	}

	/**
	 * Нужно ли сбросить данные в агрегатор.
	 */
	public function needFlush():bool {

		if (!$this->_is_enabled) {
			return false;
		}

		foreach ($this->_metric_list as $trace) {

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
		$to_flush = array_filter($this->_metric_list, static fn(Metric $el) => $el->isSealed());
		$output   = array_map(static fn(Metric $el) => $el->prepare(), $to_flush);

		// сбрасываем сохраненные данные
		$this->_metric_list = [];
		return $output;
	}
}
