<?php

namespace BaseFrame\Monitor;

/**
 * Интерфейс, который должен реализовывать класс,
 * через который будут пересылаться данные технического мониторинга.
 */
interface Sender {

	/**
	 * Выполняет отправку данных в хранилище.
	 *
	 * @param array|null $log_list
	 * @param array|null $metric_list
	 * @param array|null $trace
	 *
	 * @return void
	 */
	public function sendMonitoring(array|null $log_list, array|null $metric_list, array|null $trace):void;
}
