<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * класс-интерфейс для работы с модулем collector_server
 */
class Gateway_Socket_CollectorServer extends Gateway_Socket_Default {

	// получаем количество действий в компании по action
	public static function getSpaceActionCount(int $action, int $date_from, int $date_to):int {

		if (ServerProvider::isOnPremise()) {
			return 0;
		}

		$ar_post = [
			"action"    => $action,
			"date_from" => $date_from,
			"date_to"   => $date_to,
		];
		[$status, $response] = self::_doCallSocket("collector.getSpaceActionCount", $ar_post);
		return $response["action_count"];
	}

	/**
	 * добавляем данные оплат тарифов
	 */
	public static function setTariffData(array $file_tariff_data_list, int $is_dry):void {

		$ar_post = [
			"file_tariff_data_list" => $file_tariff_data_list,
			"is_dry"                => $is_dry,
		];
		self::_doCallSocket("analytics.setTariffData", $ar_post);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// выполняем socket запрос
	protected static function _doCallSocket(string $method, array $params, int $user_id = 0):array {

		// получаем url и подпись
		$url = self::_getSocketCollectorUrl();
		return self::_doCall($url, $method, $params, $user_id);
	}
}
