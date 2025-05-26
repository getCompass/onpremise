<?php

use \BaseFrame\Conf\ConfProvider;

/**
 * основная розетка для работы общения через шину
 */
class Bus {

	protected const _GLOBAL_KEY = "bus_grpc_connect";

	// инициализируем bus, используя готовый конфиг
	public static function configured(array $conf, string $class_name, string $key = ""):Grpc {

		// формируем ключ для хранения подключения
		$key = $key === "" ? "{$conf["host"]}:{$conf["port"]}" : $key;

		$ca_certificate = $conf["ca_certificate"] ?? "";
		$token          = $conf["token"] ?? "";

		if (!isset($GLOBALS[self::_GLOBAL_KEY][$key])) {
			$GLOBALS[self::_GLOBAL_KEY][$key] = new Grpc($conf["host"], $conf["port"], $ca_certificate, $token, $class_name);
		}

		return $GLOBALS[self::_GLOBAL_KEY][$key];
	}

	// инициализируем bus
	public static function getConnection(string $service, string $class_name):Grpc {

		$ca_certificate = $conf["ca_certificate"] ?? "";
		$token          = $conf["token"] ?? "";

		if (!isset($GLOBALS[self::_GLOBAL_KEY][$service])) {

			// получаем sharding конфиг
			$conf = ConfProvider::shardingGo()[$service];

			$GLOBALS[self::_GLOBAL_KEY][$service] = new Grpc($conf["host"], $conf["port"], $ca_certificate, $token, $class_name);
		}

		return $GLOBALS[self::_GLOBAL_KEY][$service];
	}

	// отправляет сообщение на все очереди подключенные к обменнику amq.fanout
	public static function rabbitSendToExchange(string $exchange_name, array $ar_post):void {

		// отправляем задачу в exchange
		sharding::rabbit("bus")->sendMessageToExchange($exchange_name, $ar_post);
	}

	// отправляет сообщение на конкретную очередь
	public static function rabbitSendToQueue(string $queue_name, array $ar_post):void {

		// отправляем задачу в очередь
		sharding::rabbit("bus")->sendMessage($queue_name, $ar_post);
	}

	// закрываем все возможные созданные соединения созданной шиной
	public static function end():void {

		if (!isset($GLOBALS[self::_GLOBAL_KEY])) {
			return;
		}

		foreach ($GLOBALS[self::_GLOBAL_KEY] as $client) {
			$client->end();
		}
	}
}
