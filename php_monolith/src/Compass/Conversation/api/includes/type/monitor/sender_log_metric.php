<?php

namespace Compass\Conversation;

/**
 * Временный класс, собирающий метрики производительности для поиска.
 */
class Type_Monitor_SenderLogMetric implements \BaseFrame\Monitor\Sender {

	/**
	 * Временный класс, собирающий метрики производительности для поиска.
	 */
	public function __construct(
		protected string $_file_name = "text-metric"
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function sendMonitoring(?array $log_list, ?array $metric_list, ?array $trace):void {

		if (is_null($metric_list)) {
			return;
		}

		$text = "";

		foreach ($metric_list as $metric) {

			if ((int) $metric["value"] === 0) {
				continue;
			}

			$text .= "\n{$metric["name"]}:{$metric["value"]}";
		}

		if ($text === "") {
			return;
		}

		Type_System_Admin::log($this->_file_name, "$text\n---");
	}
}