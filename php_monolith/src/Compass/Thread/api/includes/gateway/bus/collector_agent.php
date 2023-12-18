<?php

namespace Compass\Thread;

use BaseFrame\Server\ServerProvider;

/**
 * Class Gateway_Bus_CollectorAgent
 */
class Gateway_Bus_CollectorAgent {

	protected const _MODULE_NAMESPACE = CURRENT_MODULE;      // неймспейс для модуля по умолчанию

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
	 *
	 */
	public function inc(string $row):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		try {

			Type_Http_CollectorAgent::inc([
				"type"       => "TYPE_STAT_INC",
				"namespace"  => $this->_namespace,
				"company_id" => COMPANY_ID,
				"key"        => $row,
				"value"      => 1,
				"event_time" => time(),
			]);
		} catch (\cs_CurlError) {

		}
	}

	/**
	 * Выставляем кокретное значение статистики
	 *
	 */
	public function set(string $row, int $value):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		try {

			Type_Http_CollectorAgent::set([
				"type"       => "TYPE_STAT_SET",
				"namespace"  => $this->_namespace,
				"company_id" => COMPANY_ID,
				"key"        => $row,
				"value"      => $value,
				"event_time" => time(),
			]);
		} catch (\cs_CurlError) {

		}
	}

	/**
	 * Шлем дебаг
	 *
	 */
	public function add(string $event_key, array $data):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		try {

			Type_Http_CollectorAgent::add([
				"type"       => "TYPE_DEBUG_ADD",
				"namespace"  => $this->_namespace,
				"company_id" => COMPANY_ID,
				"key"        => $event_key,
				"data"       => $data,
				"event_time" => time(),
			]);
		} catch (\cs_CurlError) {

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

		if (ServerProvider::isOnPremise()) {
			return;
		}

		try {
			Type_Http_CollectorAgent::addUserEventList($user_event_list);
		} catch (\cs_CurlError) {
			// ничего не делаем если не удалось отправить
		}
	}

	/**
	 * Отправляем пользовательские счетчики
	 *
	 * @param \AnalyticUtils\Domain\Counter\Struct\User[] $user_counter_list
	 *
	 * @return void
	 */
	public function changeUserCounterList(array $user_counter_list):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		try {
			Type_Http_CollectorAgent::changeUserCounterList($user_counter_list);
		} catch (\cs_CurlError) {
			// ничего не делаем если не удалось отправить
		}
	}

	/**
	 * Шлем лог
	 *
	 */
	public function log(string $event_key, array $data):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		try {

			Type_Http_CollectorAgent::log([
				"type"       => "TYPE_DEBUG_MESSAGE",
				"namespace"  => $this->_namespace,
				"company_id" => COMPANY_ID,
				"key"        => $event_key,
				"data"       => $data,
				"event_time" => time(),
			]);
		} catch (\cs_CurlError) {

		}
	}

	/**
	 * Сохраняем кеш принудительно чтобы не ждать
	 */
	public function forceSaveCache():void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		Type_Http_CollectorAgent::forceSaveCache();
	}
}