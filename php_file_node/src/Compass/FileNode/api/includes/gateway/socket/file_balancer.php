<?php

namespace Compass\FileNode;

/**
 * Класс-интерфейс для работы с модулей file_balancer
 */
class Gateway_Socket_FileBalancer extends Gateway_Socket_Default {

	// получаем подпись из массива параметров
	public static function doCall(string $method, int $company_id, string $company_url, array $params, int $user_id = 0):array {

		// получаем url и подпись
		if ($company_id > 0) {
			$url = self::_getSocketCompanyUrl("file_balancer", $company_url);
		} else {
			$url = self::_getSocketPivotUrl("file_balancer");
		}
		return self::_doCall($url, $method, $params, $user_id, $company_id);
	}
}
