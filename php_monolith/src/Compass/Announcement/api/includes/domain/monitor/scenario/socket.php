<?php declare(strict_types = 1);

namespace Compass\Announcement;

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
		self::_collectUserAnnouncement($metric_aggregator, $need_work);
		self::_collectAnnouncementCleaner($metric_aggregator, $need_work);
		self::_collectAnnouncementCompanyUserCleaner($metric_aggregator, $need_work);
		self::_collectAnnouncementUserCompanyCleaner($metric_aggregator, $need_work);
		self::_collectAnnouncementTokenCleaner($metric_aggregator, $need_work);

		// отправляем
		Core::flush();
		return $prometheus_sender->metricToString();
	}

	// собираем метрики с таблицы announcement_user.user_announcement
	protected static function _collectUserAnnouncement(MetricAggregator $metric_aggregator, int $need_work):void {

		$table_shard_name_list = Gateway_Db_AnnouncementUser_UserAnnouncement::getAllTableShards();
		$total_value           = 0;
		$expired_value         = 0;
		foreach ($table_shard_name_list as $table_shard_name) {

			$total_value   += Gateway_Db_AnnouncementUser_UserAnnouncement::getTotalCount($table_shard_name);
			$expired_value += Gateway_Db_AnnouncementUser_UserAnnouncement::getExpiredCount($table_shard_name, $need_work);
		}
		Metric::setMetricTaskCount($metric_aggregator, "announcement_resender", $total_value);
		Metric::setMetricStaleTaskCount($metric_aggregator, "announcement_resender", $expired_value);
	}

	// собираем метрики с таблицы announcement_main.announcement
	protected static function _collectAnnouncementCleaner(MetricAggregator $metric_aggregator, int $need_work):void {

		$value = Gateway_Db_AnnouncementMain_Announcement::getTotalCount();
		Metric::setMetricTaskCount($metric_aggregator, "announcement_cleaner", $value);

		$active_status_list = Domain_Announcement_Entity::getActiveStatuses();
		$value              = Gateway_Db_AnnouncementMain_Announcement::getExpiredCount($active_status_list, $need_work);
		Metric::setMetricStaleTaskCount($metric_aggregator, "announcement_cleaner", $value);
	}

	// собираем метрики с таблицы announcement_company.company_user
	protected static function _collectAnnouncementCompanyUserCleaner(MetricAggregator $metric_aggregator, int $need_work):void {

		$table_shard_name_list = Gateway_Db_AnnouncementCompany_CompanyUser::getTableShards();
		$total_value           = 0;
		$expired_value         = 0;
		foreach ($table_shard_name_list as $table_shard_name) {

			$total_value   += Gateway_Db_AnnouncementCompany_CompanyUser::getTotalCount($table_shard_name);
			$expired_value += Gateway_Db_AnnouncementCompany_CompanyUser::getExpiredCount($table_shard_name, $need_work);
		}
		Metric::setMetricTaskCount($metric_aggregator, "company_user_cleaner", $total_value);
		Metric::setMetricStaleTaskCount($metric_aggregator, "company_user_cleaner", $expired_value);
	}

	// собираем метрики с таблицы announcement_user.user_company
	protected static function _collectAnnouncementUserCompanyCleaner(MetricAggregator $metric_aggregator, int $need_work):void {

		$table_shard_name_list = Gateway_Db_AnnouncementUser_UserCompany::getTableShards();
		$total_value           = 0;
		$expired_value         = 0;
		foreach ($table_shard_name_list as $table_shard_name) {

			$total_value   += Gateway_Db_AnnouncementUser_UserCompany::getTotalCount($table_shard_name);
			$expired_value += Gateway_Db_AnnouncementUser_UserCompany::getExpiredCount($table_shard_name, $need_work);
		}
		Metric::setMetricTaskCount($metric_aggregator, "user_company_cleaner", $total_value);
		Metric::setMetricStaleTaskCount($metric_aggregator, "user_company_cleaner", $expired_value);
	}

	// собираем метрики с таблицы announcement_security.token_user
	protected static function _collectAnnouncementTokenCleaner(MetricAggregator $metric_aggregator, int $need_work):void {

		$table_shard_name_list = Gateway_Db_AnnouncementSecurity_TokenUser::getTableShards();
		$total_value           = 0;
		$expired_value         = 0;
		foreach ($table_shard_name_list as $table_shard_name) {

			$total_value   += Gateway_Db_AnnouncementSecurity_TokenUser::getTotalCount($table_shard_name);
			$expired_value += Gateway_Db_AnnouncementSecurity_TokenUser::getExpiredCount($table_shard_name, $need_work);
		}
		Metric::setMetricTaskCount($metric_aggregator, "token_cleaner", $total_value);
		Metric::setMetricStaleTaskCount($metric_aggregator, "token_cleaner", $expired_value);
	}
}
