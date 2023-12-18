<?php

namespace Compass\FileNode;

use BaseFrame\Server\ServerProvider;

/**
 * Class Gateway_Bus_CollectorAgent
 */
class Gateway_Bus_CollectorAgent {

	protected const _MODULE_NAMESPACE = CURRENT_MODULE;      // неймспейс для модуля по умолчанию
	protected const _COMPANY_ID       = 0;

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
		} catch (\cs_CurlError) {
			// ничего не делаем если не удалось отправить

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

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * совершаем запрос
	 *
	 * @throws \cs_CurlError
	 */
	protected static function _callHttp(string $method, array $ar_post):string {

		// на on-premise не нужен этот функционал
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
		return $curl->post($path, $params);
	}
}