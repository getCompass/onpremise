<?php

namespace BaseFrame\Monitor\Helper;

use BaseFrame\Monitor\MetricAggregator;

/**
 * задача класса общаться между проектами
 * универсальная функция - общение между серверами
 */
class Metric {

	/**
	 * Устанавливаем метку модуля
	 */
	public static function setModuleLabel(MetricAggregator $metric_aggregator, string $value):void {

		$metric_aggregator->setDefaultLabel("module", $value);
	}

	/**
	 * Сохраняем метрику количества задач
	 */
	public static function setMetricTaskCount(MetricAggregator $metric_aggregator, string $name, float $value):void {

		$metric_aggregator->metric("task_count", $value, \BaseFrame\Monitor\Metric::ACCUMULATIVE, [
			"job" => $name,
		])->seal();
	}

	/**
	 * Сохраняем метрику количества зависших задач
	 */
	public static function setMetricStaleTaskCount(MetricAggregator $metric_aggregator, string $name, float $value):void {

		$metric_aggregator->metric("stale_task_count", $value, \BaseFrame\Monitor\Metric::ACCUMULATIVE, [
			"job" => $name,
		])->seal();
	}

	/**
	 * Сохраняем метрику количества не удалившихся задач в истории
	 */
	public static function setMetricStaleHistoryCount(MetricAggregator $metric_aggregator, string $name, float $value):void {

		$metric_aggregator->metric("stale_history_count", $value, \BaseFrame\Monitor\Metric::ACCUMULATIVE, [
			"job" => $name,
		])->seal();
	}

	/**
	 * Сохраняем кастомную метрику
	 */
	public static function setMetricUniqCount(MetricAggregator $metric_aggregator, string $name, float $value):void {

		$metric_aggregator->metric($name, $value, \BaseFrame\Monitor\Metric::ACCUMULATIVE)->seal();
	}
}
