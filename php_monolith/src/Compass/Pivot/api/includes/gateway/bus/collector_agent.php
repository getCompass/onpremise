<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * Class Gateway_Bus_CollectorAgent
 */
class Gateway_Bus_CollectorAgent implements \BaseFrame\Monitor\Sender {

	protected const _MODULE_NAMESPACE = CURRENT_MODULE;      // неймспейс для модуля по умолчанию
	protected const _COMPANY_ID       = 0;                   // т.е шлем с пивота

	protected string $_namespace; // неймспейс статистики

	protected function __construct(string $namespace) {

		$this->_namespace = $namespace;
	}

	// инициализируем и кладем класс в $GLOBALS
	public static function init(string $namespace = self::_MODULE_NAMESPACE):self {

		if (isset($GLOBALS[__CLASS__][$namespace])) {
			return $GLOBALS[__CLASS__][$namespace];
		}

		$GLOBALS[__CLASS__][$namespace] = new self($namespace);

		return $GLOBALS[__CLASS__][$namespace];
	}

	// -------------------------------------------------------
	// ACTIONS
	// -------------------------------------------------------

	/**
	 * Инкрементим статистику для одного эвента
	 */
	public function inc(string $row):void {

		try {

			self::_callHttp("stat.inc", [
				"type"       => "TYPE_STAT_INC",
				"namespace"  => $this->_namespace,
				"company_id" => self::_COMPANY_ID,
				"key"        => $row,
				"value"      => 1,
				"event_time" => time(),
			]);
		} catch (\cs_CurlError $e) {

			// ничего не делаем если не удалось отправить
			if (isTestServer()) {

				Type_System_Admin::log("go_collector_curl_error", [
					"company_id" => self::_COMPANY_ID,
					"row"        => $row,
					"error"      => $e->getMessage(),
				]);
			}
		}
	}

	/**
	 * Выставляем кокретное значение статистики
	 */
	public function set(string $row, int $value):void {

		try {

			self::_callHttp("stat.set", [
				"type"       => "TYPE_STAT_SET",
				"namespace"  => $this->_namespace,
				"company_id" => self::_COMPANY_ID,
				"key"        => $row,
				"value"      => $value,
				"event_time" => time(),
			]);
		} catch (\cs_CurlError) {
			// ничего не делаем если не удалось отправить
		}
	}

	/**
	 * Шлем дебаг
	 */
	public function add(string $event_key, array $data):void {

		try {

			self::_callHttp("stat.add", [
				"type"       => "TYPE_DEBUG_ADD",
				"namespace"  => $this->_namespace,
				"company_id" => self::_COMPANY_ID,
				"key"        => $event_key,
				"data"       => $data,
				"event_time" => time(),
			]);
		} catch (\cs_CurlError) {
			// ничего не делаем если не удалось отправить
		}
	}

	/**
	 * Отправляем пользовательское событие
	 *
	 * @param \AnalyticUtils\Domain\Event\Struct\User $user_event
	 *
	 * @return void
	 */
	public function addUserEvent(\AnalyticUtils\Domain\Event\Struct\User $user_event):void {

		try {

			self::_callHttp("stat.addUserEvent", $user_event->jsonSerialize());
		} catch (\cs_CurlError) {
			// ничего не делаем если не удалось отправить
		}
	}

	/**
	 * Отправляем пачку пользовательских событий
	 *
	 * @param \AnalyticUtils\Domain\Event\Struct\User[] $user_event_list
	 *
	 * @return void
	 */
	public function addUserEventList(array $user_event_list):void {

		try {

			self::_callHttp("stat.addUserEventList", [
				"user_event_list" => $user_event_list,
			]);
		} catch (\cs_CurlError) {
			// ничего не делаем если не удалось отправить
		}
	}

	/**
	 * Отправляем пачку пользовательских счетчиков
	 *
	 * @param \AnalyticUtils\Domain\Counter\Struct\User[] $user_counter_list
	 *
	 * @return void
	 */
	public function changeUserCounterList(array $user_counter_list):void {

		try {

			self::_callHttp("stat.changeUserCounterList", [
				"user_counter_list" => $user_counter_list,
			]);
		} catch (\cs_CurlError) {
			// ничего не делаем если не удалось отправить
		}
	}

	/**
	 * Шлем лог
	 */
	public function log(string $event_key, array $data):void {

		try {

			self::_callHttp("stat.log", [
				"type"       => "TYPE_DEBUG_MESSAGE",
				"namespace"  => $this->_namespace,
				"company_id" => self::_COMPANY_ID,
				"key"        => $event_key,
				"data"       => $data,
				"event_time" => time(),
			]);
		} catch (\cs_CurlError) {
			// ничего не делаем если не удалось отправить
		}
	}

	/**
	 * Сохраняем кеш принудительно чтобы не ждать
	 */
	public function forceSaveCache():void {

		try {

			self::_callHttp("stat.forceSaveCache", []);
		} catch (\cs_CurlError) {
			// ничего не делаем если не удалось отправить
		}
	}

	/**
	 * Сбрасываем данные мониторинга.
	 *
	 * @param array|null $log_list
	 * @param array|null $trace
	 * @param array|null $metric_list
	 */
	public function sendMonitoring(array|null $log_list, array|null $metric_list, array|null $trace):void {

		try {

			self::_callHttp("monitor.push", array_filter([
				"log_list"    => $log_list,
				"metric_list" => $metric_list,
				"trace"       => $trace,
			]), 1);
		} catch (\Exception | \Error) {

			// ничего не делаем если не удалось отправить
		}
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * совершаем запрос
	 *
	 * @throws \cs_CurlError
	 */
	protected static function _callHttp(string $method, array $ar_post, int $timeout = 0):string {

		if (ServerProvider::isOnPremise()) {
			return "";
		}

		// получаем конфиг
		$config = getConfig("SHARDING_GO");

		$params = [
			"method"  => "$method",
			"request" => toJson($ar_post),
		];

		$path = $config["collector_agent"]["protocol"] . "://" . $config["collector_agent"]["host"] . ":" . $config["collector_agent"]["port"];
		$curl = new \Curl();

		if ($timeout !== 0) {
			$curl->setTimeout(1);
		}

		return $curl->post($path, $params);
	}
}