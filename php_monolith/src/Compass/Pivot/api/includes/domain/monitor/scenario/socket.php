<?php declare(strict_types = 1);

namespace Compass\Pivot;

use BaseFrame\Monitor\Core;
use BaseFrame\Monitor\MetricAggregator;
use BaseFrame\Monitor\Helper\Metric;

/**
 * Сценарии мониторинга для prometheus
 */
class Domain_Monitor_Scenario_Socket {

	/**
	 * Собираем метрики
	 *
	 * @return string
	 */
	public static function collect():string {

		$need_work = time() - 60 * 5;

		// инициализируем
		$prometheus_sender = \BaseFrame\Monitor\PrometheusSender::init();
		Core::init($prometheus_sender, false, true, false);
		$metric_aggregator = Core::getMetricAggregator();
		Metric::setModuleLabel($metric_aggregator, CURRENT_MODULE);

		// собираем аналитику
		self::_collectCompanyServiceTask($metric_aggregator, $need_work);
		self::_collectCompanyTaskQueue($metric_aggregator, $need_work);
		self::_collectSmsObserverProvider($metric_aggregator, $need_work);
		self::_collectSmsSendQueue($metric_aggregator, $need_work);
		self::_collectPremiumStatusObserve($metric_aggregator, $need_work);
		self::_collectCompanyTierObserve($metric_aggregator, $need_work);
		self::_collectPhphookerQueue($metric_aggregator, $need_work);
		self::_collectFailedBitrixTasks($metric_aggregator, $need_work);

		// отправляем
		Core::flush();
		return $prometheus_sender->metricToString();
	}

	// собираем метрики с таблицы pivot_company_service.company_service_task
	protected static function _collectCompanyServiceTask(MetricAggregator $metric_aggregator, int $need_work):void {

		$queue_name  = "company_service_task";
		$total_value = Gateway_Db_PivotCompanyService_CompanyServiceTask::getTotalCount();
		Metric::setMetricTaskCount($metric_aggregator, $queue_name, $total_value);

		$expired_failed_value = Gateway_Db_PivotCompanyService_CompanyServiceTask::getExpiredCount(1, $need_work);
		Metric::setMetricStaleTaskCount($metric_aggregator, "{$queue_name}_failed", $expired_failed_value);

		$expired_not_failed_value = Gateway_Db_PivotCompanyService_CompanyServiceTask::getExpiredCount(0, $need_work);
		Metric::setMetricStaleTaskCount($metric_aggregator, "{$queue_name}_not_failed", $expired_not_failed_value);

		$history_value = Gateway_Db_PivotCompanyService_CompanyServiceTaskHistory::getHistoryCount(minuteStart() - DAY1 * 61);
		Metric::setMetricStaleHistoryCount($metric_aggregator, $queue_name, $history_value);
	}

	// собираем метрики с таблицы pivot_data.company_task_queue
	protected static function _collectCompanyTaskQueue(MetricAggregator $metric_aggregator, int $need_work):void {

		$queue_name  = "company_task_queue";
		$total_value = Gateway_Db_PivotData_CompanyTaskQueue::getTotalCount();
		Metric::setMetricTaskCount($metric_aggregator, $queue_name, $total_value);

		$expired_value = Gateway_Db_PivotData_CompanyTaskQueue::getExpiredCount(Domain_Company_Entity_CronCompanyTask::STATUS_IN_PROGRESS, $need_work);
		Metric::setMetricStaleTaskCount($metric_aggregator, $queue_name, $expired_value);
	}

	// собираем метрики с таблицы pivot_sms_service.observer_provider
	protected static function _collectSmsObserverProvider(MetricAggregator $metric_aggregator, int $need_work):void {

		$queue_name  = "sms_observer_provider";
		$total_value = Gateway_Db_PivotSmsService_ObserverProvider::getTotalCount();
		Metric::setMetricTaskCount($metric_aggregator, $queue_name, $total_value);

		$expired_value = Gateway_Db_PivotSmsService_ObserverProvider::getExpiredCount($need_work);
		Metric::setMetricStaleTaskCount($metric_aggregator, $queue_name, $expired_value);
	}

	// собираем метрики с таблицы pivot_sms_service.send_queue
	protected static function _collectSmsSendQueue(MetricAggregator $metric_aggregator, int $need_work):void {

		$queue_name  = "sms_send_queue";
		$total_value = Gateway_Db_PivotSmsService_SendQueue::getTotalCount();
		Metric::setMetricTaskCount($metric_aggregator, $queue_name, $total_value);

		$expired_value = Gateway_Db_PivotSmsService_SendQueue::getExpiredCount($need_work);
		Metric::setMetricStaleTaskCount($metric_aggregator, $queue_name, $expired_value);
	}

	// собираем метрики с таблицы pivot_user.premium_status_observe
	protected static function _collectPremiumStatusObserve(MetricAggregator $metric_aggregator, int $need_work):void {

		// пробегаем по всем шардам
		$total_value   = 0;
		$expired_value = 0;
		$queue_name    = "premium_status_observe";
		foreach (range(1, 10_000_000, 1_000_000) as $shard_user_id) {

			$total_value   += Gateway_Db_PivotUser_PremiumStatusObserve::getTotalCount($shard_user_id);
			$expired_value += Gateway_Db_PivotUser_PremiumStatusObserve::getExpiredCount($shard_user_id, $need_work);
		}
		Metric::setMetricTaskCount($metric_aggregator, $queue_name, $total_value);
		Metric::setMetricStaleTaskCount($metric_aggregator, $queue_name, $expired_value);
	}

	// собираем метрики с таблицы pivot_company.company_tier_observe
	protected static function _collectCompanyTierObserve(MetricAggregator $metric_aggregator, int $need_work):void {

		// пробегаем по всем шардам
		$total_value   = 0;
		$expired_value = 0;
		$queue_name    = "company_tier_observe";
		foreach (range(1, 10_000_000, 1_000_000) as $shard_company_id) {

			$total_value   += Gateway_Db_PivotCompany_CompanyTierObserve::getTotalCount($shard_company_id);
			$expired_value += Gateway_Db_PivotCompany_CompanyTierObserve::getExpiredCount($shard_company_id, $need_work);
		}
		Metric::setMetricTaskCount($metric_aggregator, $queue_name, $total_value);
		Metric::setMetricStaleTaskCount($metric_aggregator, $queue_name, $expired_value);
	}

	// собираем метрики с таблицы pivot_system.phphooker_queue
	protected static function _collectPhphookerQueue(MetricAggregator $metric_aggregator, int $need_work):void {

		$queue_name  = "phphooker_queue";
		$total_value = Gateway_Db_PivotSystem_PhphookerQueue::getTotalCount();
		Metric::setMetricTaskCount($metric_aggregator, $queue_name, $total_value);

		$expired_value = Gateway_Db_PivotSystem_PhphookerQueue::getExpiredCount($need_work);
		Metric::setMetricStaleTaskCount($metric_aggregator, $queue_name, $expired_value);
	}

	// собираем количество проваленных задач для битрикса
	protected static function _collectFailedBitrixTasks(MetricAggregator $metric_aggregator, int $need_work):void {

		$queue_name  = "bitrix_failed_task";
		$total_value = Gateway_Db_PivotBusiness_BitrixUserInfoFailedTaskList::getCount();
		Metric::setMetricTaskCount($metric_aggregator, $queue_name, $total_value);
	}
}
