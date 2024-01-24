<?php

namespace Compass\Thread;

/**
 * класс-интерфейс для работы с модулей file_dpc
 */
class Gateway_Socket_FileBalancer extends Gateway_Socket_Default {

	// получаем подпись из массива параметров
	public static function doCall(string $method, array $params, int $user_id = 0):array {

		// получаем url и подпись
		$url = self::_getSocketCompanyUrl("file_balancer");
		return self::_doCall($url, $method, $params, $user_id);
	}
}
