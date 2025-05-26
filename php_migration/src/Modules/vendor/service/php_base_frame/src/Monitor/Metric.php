<?php

namespace BaseFrame\Monitor;

use BaseFrame\Exception\Domain\ReturnFatalException;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Объект метрики.
 * Метрики по сути снимаются как пара ключ-значение и не содержат никаких хитростей.
 */
class Metric {

	public const PRECISION_S  = "s";
	public const PRECISION_MS = "ms";
	public const PRECISION_US = "us";
	public const PRECISION_NS = "ns";

	public const INCREMENTAL  = 1; // инкрементальная (по сути просто счетчик)
	public const ACCUMULATIVE = 2; // накопительная (высчитывается среднее/минимальное/максимальное за промежуток времени)

	protected string $_name;            // имя метрики
	protected float  $_value;           // значение для метрики
	protected int    $_behaviour;       // поведение метрики (инкрементальная/накопительная)
	protected array  $_label_list = []; // список меток метрики

	protected float $_start_at; // дата начала снятия метрики

	protected bool $_is_sealed = false; // завершен ли сбор метрики

	/**
	 * Конструктор
	 * @throws ReturnFatalException
	 */
	public function __construct(string $name, float $value, int $behaviour) {

		if ($behaviour !== self::INCREMENTAL && $behaviour !== self::ACCUMULATIVE) {
			throw new ReturnFatalException("passed bad metric type $behaviour");
		}

		$this->_name      = $name;
		$this->_behaviour = $behaviour;
		$this->_value     = $value;
		$this->_start_at  = timeNs();
	}

	/**
	 * Добавляет метку к метрике.
	 */
	public function label(string $label_name, string|int|float $value):static {

		$this->_label_list[$label_name] = (string) $value;
		return $this;
	}

	/**
	 * Фиксирует время с момента создания метрики и использует его как значение.
	 * <b>Если значение ранее было установлено руками, то перезаписывает его.</b>
	 */
	public function since(string $precision = self::PRECISION_MS):static {

		$this->_value = timeNs() - $this->_start_at;

		if ($precision === self::PRECISION_US) {
			$this->_value /= 1_000;
		}

		if ($precision === self::PRECISION_MS) {
			$this->_value /= 1_000_000;
		}

		if ($precision === self::PRECISION_S) {
			$this->_value /= 1_000_000_000;
		}

		return $this;
	}

	/**
	 * Запечатывает метрику.
	 * Любая метрика должна быть запечатана, иначе она не будет передан в сборщик.
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
	 * Конвертирует объект в массив с нужными полями.
	 */
	#[ArrayShape(["name" => "string", "behaviour" => "int", "value" => "float", "label_list" => "array"])]
	public function prepare():array {

		return [
			"name"       => $this->_name,
			"behaviour"  => $this->_behaviour,
			"value"      => $this->_value,
			"label_list" => $this->_label_list,
		];
	}
}