<?php

namespace BaseFrame\Monitor;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Общий класс для управления мониторингом
 */
class Core {

	protected static Sender $_gateway;
	protected static bool   $_is_initialized = false;
	protected static bool   $_is_closed      = false;

	/**
	 * Закрываем конструктор.
	 */
	protected function __construct() {

	}

	/**
	 * Возвращает экземпляр класса trace-мониторинга.
	 */
	public static function getTraceAggregator():TraceAggregator {

		return TraceAggregator::instance();
	}

	/**
	 * Возвращает экземпляр класса trace-мониторинга.
	 */
	public static function trace(string $message):TraceLine {

		return TraceAggregator::instance()->trace($message);
	}

	/**
	 * Возвращает экземпляр класса текстовых логов.
	 */
	public static function getLogAggregator():LogAggregator {

		return LogAggregator::instance();
	}

	/**
	 * Возвращает экземпляр класса текстовых логов.
	 */
	public static function log(string $message, int $log_level = LogLine::LOG_LEVEL_INFO):LogLine {

		return LogAggregator::instance()->log($message, $log_level);
	}

	/**
	 * Возвращает экземпляр класса агрегатора метрик.
	 */
	public static function getMetricAggregator():MetricAggregator {

		return MetricAggregator::instance();
	}

	/**
	 * Возвращает экземпляр класса текстовых логов.
	 * @throws ReturnFatalException
	 */
	public static function metric(string $name, float $value = 0, int $behaviour = Metric::ACCUMULATIVE):Metric {

		return MetricAggregator::instance()->metric($name, $value, $behaviour);
	}

	/**
	 * По необходимости закрывает сбрасывает все накопившуюся информацию
	 * и запрещает дальнейшую отправку данных. Такая штука нужна, чтобы
	 * случайно не возникла зацикленная обработка ошибок.
	 *
	 * Вызываться нужна тогда, когда процесс должен умереть в результате ошибки.
	 */
	public static function close(bool $need_flush = true):void {

		if ($need_flush) {

			try {
				static::flush();
			} catch (\Throwable) {

			}
		}

		static::$_is_closed = true;
	}

	/**
	 * Сбрасывает результаты сбора данных для мониторинга в хранилище.
	 */
	public static function flush():void {

		if (!static::$_is_initialized || static::$_is_closed) {
			return;
		}

		$trace       = static::getTraceAggregator()->needFlush() ? static::getTraceAggregator()->flush() : null;
		$log_list    = static::getLogAggregator()->needFlush() ? static::getLogAggregator()->flush() : null;
		$metric_list = static::getMetricAggregator()->needFlush() ? static::getMetricAggregator()->flush() : null;

		// данных мониторинга нет, выходим
		if (is_null($trace) && is_null($log_list) && is_null($metric_list)) {
			return;
		}

		static::$_gateway->sendMonitoring($log_list, $metric_list, $trace);
	}

	/**
	 * Выполняет предварительную настройку класса мониторинга.
	 */
	public static function init(Sender $gateway, bool $enable_logs, bool $enable_metrics, bool $enable_tracing):void {

		static::$_gateway        = $gateway;
		static::$_is_initialized = true;

		static::getLogAggregator()->set($enable_logs);
		static::getMetricAggregator()->set($enable_metrics);
		static::getTraceAggregator()->set($enable_tracing);
	}
}