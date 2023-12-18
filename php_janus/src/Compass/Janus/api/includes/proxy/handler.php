<?php

namespace Compass\Janus;

/**
 * Класс обработки входящих запросов на /janus точку входа.
 */
class Proxy_Handler implements \RouteHandler {

	/**
	 * @inheritDoc
	 */
	public function getServedRoutes():array {

		return ["/"];
	}

	/**
	 * @inheritDoc
	 */
	public function getType():string {

		return "proxy";
	}

	/**
	 * @inheritDoc
	 */
	public function __toString():string {

		return static::class;
	}

	/**
	 * @inheritDoc
	 */
	public function handle(string $route, array $post_data):array {

		$request_data = file_get_contents("php://input");
		$output       = [];

		foreach (fromJson($request_data) as $event) {

			if (!isset($event["opaque_id"])) {
				continue;
			}

			$output[$event["opaque_id"]] = $event;
		}

		foreach ($output as $k => $v) {

			// устанавливаем все опции и осуществляем запрос
			// инициируем curl
			$curl = curl_init();

			// задаем загаловки
			curl_setopt($curl, CURLOPT_HTTPHEADER, [
				"Content-type: application/json",
			]);

			$url = "https://" . $k . "/janus/";

			// задаем опции
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_POST, "true");
			curl_setopt($curl, CURLOPT_POSTFIELDS, $request_data);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_URL, $url);

			// передаем данные приложение
			curl_exec($curl);
		}

		return [];
	}
}