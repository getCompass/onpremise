<?php

namespace Compass\FileBalancer;

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
	 * Шлем эвент лог
	 *
	 * @throws \cs_CurlError
	 */
	public static function log(array $event):string {

		return self::_callHttp("stat.log", $event);
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
			"method"  => "$method",
			"request" => toJson($ar_post),
		];

		if (defined("COMPANY_ID") && COMPANY_ID > 0) {
			$params["company_id"] = COMPANY_ID;
		}

		$path = "http://" . $config["collector_agent"]["host"] . ":" . $config["collector_agent"]["port"];

		$curl = new \Curl();
		return $curl->post($path, $params);
	}
}