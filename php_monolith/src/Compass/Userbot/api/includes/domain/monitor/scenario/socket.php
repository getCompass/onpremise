<?php

declare(strict_types = 1);

namespace Compass\Userbot;

use BaseFrame\Monitor\Core;
use BaseFrame\Monitor\MetricAggregator;
use BaseFrame\Monitor\Helper\Metric;

/**
 * Сценарии мониторинга для prometheus
 */
class Domain_Monitor_Scenario_Socket {

	/**
	 * Собираем метрики
	 */
	public static function collect():string {

		$need_work = time() - 60 * 5;

		// инициализируем
		$prometheus_sender = \BaseFrame\Monitor\PrometheusSender::init();
		Core::init($prometheus_sender, false, true, false);
		$metric_aggregator = Core::getMetricAggregator();
		Metric::setModuleLabel($metric_aggregator, CURRENT_MODULE);

		// собираем аналитику
		self::_collectCommandQueue($metric_aggregator, $need_work);
		self::_collectRequestList($metric_aggregator, $need_work);

		// отправляем
		Core::flush();
		return $prometheus_sender->metricToString();
	}

	// собираем метрики с таблицы userbot_main.command_queue
	protected static function _collectCommandQueue(MetricAggregator $metric_aggregator, int $need_work):void {

		$queue_name  = "command_queue";
		$total_value = Gateway_Db_UserbotMain_CommandQueue::getTotalCount();
		Metric::setMetricTaskCount($metric_aggregator, $queue_name, $total_value);

		$expired_value = Gateway_Db_UserbotMain_CommandQueue::getExpiredCount($need_work);
		Metric::setMetricStaleTaskCount($metric_aggregator, $queue_name, $expired_value);
	}

	// собираем метрики с таблицы userbot_main.request_list
	protected static function _collectRequestList(MetricAggregator $metric_aggregator, int $need_work):void {

		$queue_name  = "request_list";
		$total_value = Gateway_Db_UserbotMain_RequestList::getTotalCount();
		Metric::setMetricTaskCount($metric_aggregator, $queue_name, $total_value);

		$expired_value = Gateway_Db_UserbotMain_RequestList::getExpiredCount($need_work);
		Metric::setMetricStaleTaskCount($metric_aggregator, $queue_name, $expired_value);
	}
}
