<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Server\ServerProvider;

/**
 * Гейтвей для общения с биллингом
 */
class Gateway_Socket_Billing {

	public static function useTestPremium(int $user_id, string $goods_id, string $phone_hash):void {

		if (isTestServer() || ServerProvider::isOnPremise()) {
			return;
		}

		// формируем параметры для запроса
		$params = [
			"user_id"    => (int) $user_id,
			"goods_id"   => (string) $goods_id,
			"phone_hash" => (string) $phone_hash,
		];

		[$status, $response] = self::_call("pivot.newTestPremium", $params);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}
	}

	/**
	 * Получаем выручку
	 *
	 * @param int $from_date
	 * @param int $to_date
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getRevenueStat(int $from_date, int $to_date):array {

		if (isTestServer() || ServerProvider::isOnPremise()) {
			return [];
		}

		// формируем параметры для запроса
		$params = [
			"date_from" => (int) $from_date,
			"date_to"   => (int) $to_date,
		];

		[$status, $response] = self::_call("pivot.getRevenueStat", $params);
		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}

		return $response;
	}

	/**
	 * создаём корзину товаров
	 *
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \parseException
	 * @throws \returnException
	 * @long
	 */
	public static function createBasket(int $user_id, array $goods_id_list):array {

		if (ServerProvider::isOnPremise()) {
			return [];
		}

		// для тестового - просто мокаем
		if (isTestServer()) {

			[$status, $response] = Gateway_Socket_Billing_Mock::doCall("pivot.createBasket", [
				"sum"        => "666.00",
				"currency"   => "rub",
				"goods_list" => $goods_id_list,
			], 0);

			if ($status != "ok") {

				if (!isset($response["error_code"])) {
					throw new ReturnFatalException("wrong response");
				}

				throw new ParseFatalException("passed unknown error_code");
			}

			return [
				$response["good_id"],
				$response["sum"],
				$response["currency"],
				$response["goods_list"],
			];
		}

		// формируем параметры для запроса
		$params = [
			"user_id"       => (int) $user_id,
			"goods_id_list" => (array) $goods_id_list,
		];

		[$status, $response] = self::_call("pivot.createBasket", $params);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}

		return [
			$response["good_id"],
			$response["sum"],
			$response["currency"],
			$response["goods_list"],
		];
	}

	/**
	 * делаем вызов
	 *
	 * @param string $method
	 * @param array  $params
	 *
	 * @return array
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _call(string $method, array $params):array {

		if (ServerProvider::isOnPremise()) {
			return [];
		}

		// переводим в json параметры
		$json_params = toJson($params);

		// получаем url и подпись
		$url       = self::_getUrl();
		$signature = Type_Socket_Auth_Handler::getSignature(Type_Socket_Auth_Handler::AUTH_TYPE_KEY, SOCKET_KEY_PIVOT, $json_params);

		// совершаем запрос
		return Type_Socket_Main::doCall($url, $method, $json_params, $signature);
	}

	/**
	 * получаем url
	 *
	 */
	protected static function _getUrl():string {

		$socket_module_config = getConfig("SOCKET_MODULE");
		return sprintf("%s%s", BILLING_URL, $socket_module_config["billing"]["socket_path"]);
	}

}