<?php

namespace Compass\Speaker;

/**
 * класс-интерфейс для работы с модулем company
 */
class Gateway_Socket_Company extends Gateway_Socket_Default {

	/**
	 * Существует ли такая компания
	 *
	 * @throws \returnException
	 */
	public static function exists():bool {

		$api_response = self::doCall("company.main.exists", []);
		$status       = $api_response["status"];
		$response     = $api_response["response"];

		// если сокет-запрос не вернул ok
		if ($status != "ok") {
			$txt = toJson($response);
			throw new \returnException("Socket request member.getUserRoleList status != ok. Response: {$txt}");
		}

		return $response["exists"];
	}

	// получаем подпись из массива параметров
	public static function doCall(string $method, array $params, int $user_id = 0):array {

		// получаем url и подпись
		$url = self::_getSocketCompanyUrl("company");
		return self::_doCall($url, $method, $params, $user_id);
	}
}
