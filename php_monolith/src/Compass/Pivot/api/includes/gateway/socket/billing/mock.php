<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * класс для мокинга socket-вызовов модуля биллинга
 */
class Gateway_Socket_Billing_Mock {

	/**
	 * эмулируем запрос в биллинг
	 *
	 * @return array
	 * @throws \parseException
	 * @noinspection PhpUnusedParameterInspection
	 */
	public static function doCall(string $method, array $params, int $user_id):array {

		if (ServerProvider::isOnPremise()) {

			return [
				"status"   => "",
				"response" => [],
			];
		}

		try {
			$output = self::_getCache($method);
		} catch (cs_CacheIsEmpty) {

			$output = match ($method) {
				"pivot.createBasket" => self::_mockCreateBasket($params),
				default => throw new \parseException("unexpected behaviour"),
			};
		}

		return [$output["status"], $output["response"]];
	}

	/**
	 * мокаем ответ метода billing.createBasket
	 *
	 * @return array
	 */
	protected static function _mockCreateBasket(array $params):array {

		return [
			"status"   => "ok",
			"response" => [
				"good_id"    => "basket_" . generateUUID(),
				"sum"        => $params["sum"],
				"currency"   => $params["currency"],
				"goods_list" => $params["goods_list"],
			],
		];
	}

	/**
	 * возвращаем пустой ответ ok
	 *
	 * @return array
	 */
	public static function mockEmptyOkResponse():array {

		return [
			"status"   => "ok",
			"response" => [],
		];
	}

	/**
	 * получить мок данные из кэша
	 *
	 * @param string $key
	 *
	 * @return mixed
	 * @throws cs_CacheIsEmpty
	 */
	protected static function _getCache(string $key):mixed {

		$result = ShardingGateway::cache()->get(self::_getKey($key));
		if ($result === false) {
			throw new cs_CacheIsEmpty();
		}

		return $result;
	}

	/**
	 * получаем ключ
	 *
	 * @return string
	 */
	protected static function _getKey(string $key):string {

		return __CLASS__ . $key;
	}
}