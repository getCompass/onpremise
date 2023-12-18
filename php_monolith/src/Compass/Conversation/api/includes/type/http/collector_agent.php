<?php

namespace Compass\Conversation;

use BaseFrame\Server\ServerProvider;

/**
 * Класс для работы с go-collector-agent
 */
class Type_Http_CollectorAgent {

	/**
	 * Инкрементим статистику
	 *
	 * @throws \cs_CurlError
	 */
	public static function inc(array $event):string {

		return self::_callHttp("stat.inc", $event);
	}

	/**
	 * Выставляем конкретное значение статистику
	 *
	 * @throws \cs_CurlError
	 */
	public static function set(array $event):string {

		return self::_callHttp("stat.set", $event);
	}

	/**
	 * Шлем эвент маяк
	 *
	 * @throws \cs_CurlError
	 */
	public static function add(array $event):string {

		return self::_callHttp("stat.add", $event);
	}

	/**
	 * Отправляем пачку пользовательских событий
	 *
	 * @param \AnalyticUtils\Domain\Event\Struct\User[] $user_event_list
	 *
	 * @return void
	 */
	public static function addUserEventList(array $user_event_list):void {

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
	public static function changeUserCounterList(array $user_counter_list):void {

		try {

			self::_callHttp("stat.changeUserCounterList", [
				"user_counter_list" => $user_counter_list,
			]);
		} catch (\cs_CurlError) {
			// ничего не делаем если не удалось отправить
		}
	}

	/**
	 * Шлем эвент лог
	 *
	 * @param array $event
	 */
	public static function log(array $event):void {

		try {

			self::_callHttp("stat.log", $event);
		} catch (\cs_CurlError) {
			// ничего не делаем если не удалось отправить
		}
	}

	/**
	 * Сохраняем кеш принудительно чтобы не ждать
	 */
	public static function forceSaveCache():void {

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
	public static function sendMonitoring(array|null $log_list, array|null $metric_list, array|null $trace):void {

		try {

			self::_callHttp("monitor.push", array_filter([
				"log_list"    => $log_list,
				"metric_list" => $metric_list,
				"trace"       => $trace,
			]));
		} catch (\Exception|\Error) {

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
	protected static function _callHttp(string $method, array $ar_post):string {

		if (ServerProvider::isOnPremise()) {
			return "";
		}

		// получаем конфиг
		$config = getConfig("SHARDING_GO");

		$params = [
			"method"     => "$method",
			"company_id" => COMPANY_ID,
			"request"    => toJson($ar_post),
		];

		$path = $config["collector_agent"]["protocol"] . "://" . $config["collector_agent"]["host"] . ":" . $config["collector_agent"]["port"];

		$curl = new \Curl();
		return $curl->post($path, $params);
	}
}