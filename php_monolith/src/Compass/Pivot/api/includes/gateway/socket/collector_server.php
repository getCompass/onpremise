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
	 * Получаем список пользователей, совершивших action за переданный интервал
	 *
	 * @return array
	 */
	public static function getSpaceActionUserList(int $action, int $date_from, int $date_to):array {

		$ar_post = [
			"action"    => $action,
			"date_from" => $date_from,
			"date_to"   => $date_to,
		];
		[$status, $response] = self::_doCallSocket("collector.getSpaceActionUserList", $ar_post);
		return $response["user_list"];
	}

	/**
	 * Получаем аналитику по join-трафику
	 *
	 * @return array
	 */
	public static function getJoinSpaceMetricsByInterval(int $date_from, int $date_to):array {

		$ar_post = [
			"from_date" => $date_from,
			"to_date"   => $date_to,
		];
		[$status, $response] = self::_doCallSocket("attribution.getJoinSpaceMetricsByInterval", $ar_post);
		return [$response["app_registered_user_counter"], $response["app_total_enter_space_counter"]];
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

	/**
	 * Получаем статистику
	 *
	 * @return array
	 */
	public static function getRowByDate(string $namespace, string $row_name, string $period_type, int $date_start, int $date_end):array {

		$ar_post = [
			"namespace"   => $namespace,
			"row_name"    => $row_name,
			"period_type" => $period_type,
			"date_start"  => $date_start,
			"date_end"    => $date_end,
		];
		[$status, $response] = self::_doCallSocket("collector.getRowByDate", $ar_post);

		return $response["row_data"];
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
