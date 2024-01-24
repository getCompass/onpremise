<?php

namespace Compass\Conversation;

/**
 * класс-интерфейс для работы с модулей speaker
 */
class Gateway_Socket_Speaker extends Gateway_Socket_Default {

	// получаем подпись из массива параметров
	public static function doCall(string $method, array $params, int $user_id = 0):array {

		// получаем url и подпись
		$url = self::_getSocketCompanyUrl("speaker");
		return self::_doCall($url, $method, $params, $user_id);
	}
}
