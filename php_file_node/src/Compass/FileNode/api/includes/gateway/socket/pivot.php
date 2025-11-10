<?php

namespace Compass\FileNode;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс-интерфейс для работы с модулем pivot
 */
class Gateway_Socket_Pivot extends Gateway_Socket_Default {

	/**
	 * Получить ентрипоинты для file_balancer компаний
	 *
	 * @param array $company_id_list
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_CompanyIsHibernated
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ReturnFatalException
	 * @throws \cs_SocketRequestIsFailed
	 */
	public static function getEntrypointList(array $company_id_list):array {

		// отправляем socket запрос для получения ентрипоинтов компаний
		[$status, $response] = self::doCall("pivot.company.getEntrypointList", [
			"company_id_list" => $company_id_list,
		]);

		// если не ок — бросаем экшепшен
		if ($status != "ok" || !isset($response["entrypoint_list"])) {
			throw new ReturnFatalException("Unhandled error_code from socket call in " . __METHOD__);
		}

		return $response["entrypoint_list"];
	}

	/**
	 * Вызываем метод
	 *
	 * @param string $method
	 * @param array  $params
	 * @param int    $user_id
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_CompanyIsHibernated
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \cs_SocketRequestIsFailed
	 */
	public static function doCall(string $method, array $params, int $user_id = 0):array {

		// получаем url и подпись
		$url = self::_getSocketPivotUrl("pivot");
		return self::_doCall($url, $method, $params, $user_id);
	}
}
