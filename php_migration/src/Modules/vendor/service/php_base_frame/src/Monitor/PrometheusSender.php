<?php

namespace BaseFrame\Monitor;

use BaseFrame\Module\ModuleProvider;

/**
 * Класс для работы с метриками очередей
 */
class PrometheusSender implements Sender {

	protected string $_module; // модуль
	protected array  $_metric_list; // метрики

	protected function __construct(string $module) {

		$this->_module      = $module;
		$this->_metric_list = [];
	}

	// инициализируем и кладем класс в $GLOBALS
	public static function init(string|false $module = false):self {

		if (!$module) {
			$module = ModuleProvider::current();
		}

		if (isset($GLOBALS[__CLASS__][$module])) {
			return $GLOBALS[__CLASS__][$module];
		}

		$GLOBALS[__CLASS__][$module] = new self($module);

		return $GLOBALS[__CLASS__][$module];
	}

	/**
	 * Сбрасываем данные мониторинга.
	 *
	 * @param array|null $log_list
	 * @param array|null $trace
	 * @param array|null $metric_list
	 */
	public function sendMonitoring(array|null $log_list, array|null $metric_list, array|null $trace):void {

		$this->_metric_list = $metric_list;
	}

	/**
	 * Форматируем метрики в строку
	 *
	 * @return string
	 */
	public function metricToString():string {

		$output = "";
		foreach ($this->_metric_list as $item) {

			$label_list = "";
			foreach ($item["label_list"] as $label_key => $label_value) {
				$label_list .= "{$label_key}=\"{$label_value}\",";
			}
			$label_list = rtrim($label_list, ",");
			$output     .= "{$item["name"]} {{$label_list}} {$item["value"]}\n";
		}
		return trim($output);
	}
}
