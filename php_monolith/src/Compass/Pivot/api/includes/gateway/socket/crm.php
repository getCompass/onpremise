<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;
use Tariff\Plan\MemberCount\MemberCount;

/**
 * класс-интерфейс для работы с модулем CRM
 */
class Gateway_Socket_Crm extends Gateway_Socket_Default {

	/**
	 * Оповещаем о новой оплате в пространстве
	 * @long
	 */
	public static function onNewPayment(
		int         $space_id,
		string      $payment_id,
		string      $payed_currency,
		int         $payed_amount,
		int         $net_amount_rub,
		string      $payment_method,
		int         $payment_price_type,
		MemberCount $member_count,
		int         $tariff_prolongation_duration,
	):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		try {

			/** @noinspection PhpUnusedLocalVariableInspection */
			[$status, $response] = self::_call("crm.onNewPayment", [
				"space_id"                     => $space_id,
				"payment_id"                   => $payment_id,
				"payed_currency"               => $payed_currency,
				"payed_amount"                 => $payed_amount,
				"net_amount_rub"               => $net_amount_rub,
				"payment_method"               => $payment_method,
				"payment_price_type"           => $payment_price_type,
				"tariff_member_limit"          => $member_count->getLimit(),
				"tariff_prolongation_duration" => $tariff_prolongation_duration,
				"tariff_active_till"           => $member_count->getActiveTill(),
				"tariff_is_trial"              => $member_count->isTrial(time()),
				"tariff_is_paid"               => $member_count->isActive(time()) && !$member_count->isFree(time()),
			]);
		} catch (\cs_SocketRequestIsFailed $e) {

			// логируем исключение, но не падаем, чтобы не заафектить функционал оплаты
			$exception_message = \BaseFrame\Exception\ExceptionUtils::makeMessage($e, 0);
			\BaseFrame\Exception\ExceptionUtils::writeExceptionToLogs($e, $exception_message);
		}
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
			return ["", []];
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

		$socket_url           = getConfig("SOCKET_URL");
		$socket_module_config = getConfig("SOCKET_MODULE");
		return sprintf("%s%s", $socket_url["crm"], $socket_module_config["crm"]["socket_path"]);
	}
}
