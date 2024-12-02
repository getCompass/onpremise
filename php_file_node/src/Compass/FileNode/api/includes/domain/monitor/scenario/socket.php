<?php declare(strict_types = 1);

namespace Compass\FileNode;

use BaseFrame\Monitor\Core;
use BaseFrame\Monitor\MetricAggregator;
use BaseFrame\Monitor\Helper\Metric;
use BaseFrame\Server\ServerProvider;

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
		Metric::setModuleLabel($metric_aggregator, CURRENT_MODULE . NODE_ID);

		// собираем аналитику
		self::_collectPostUploadQueue($metric_aggregator, "post_upload_audio", FILE_TYPE_AUDIO, $need_work);
		self::_collectPostUploadQueue($metric_aggregator, "post_upload_image", FILE_TYPE_IMAGE, $need_work);
		self::_collectPostUploadQueue($metric_aggregator, "post_upload_video", FILE_TYPE_VIDEO, $need_work);
		self::_collectPostUploadQueue($metric_aggregator, "post_upload_document", FILE_TYPE_DOCUMENT, $need_work);

		// отправляем
		Core::flush();

		return $prometheus_sender->metricToString();
	}

	// собираем метрики с таблицы file_node.post_upload_queue
	protected static function _collectPostUploadQueue(MetricAggregator $metric_aggregator, string $name, int $file_type, int $need_work):void {

		$total_value = Gateway_Db_FileNode_PostUpload::getTotalCount($file_type);
		Metric::setMetricTaskCount($metric_aggregator, $name, $total_value);

		$expired_value = Gateway_Db_FileNode_PostUpload::getExpiredCount($need_work, $file_type);
		Metric::setMetricStaleTaskCount($metric_aggregator, $name, $expired_value);
	}
}
