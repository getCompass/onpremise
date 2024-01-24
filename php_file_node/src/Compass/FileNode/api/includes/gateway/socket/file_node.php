<?php

namespace Compass\FileNode;

/**
 * Класс-интерфейс для работы с модулей file_node
 */
class Gateway_Socket_FileNode extends Gateway_Socket_Default {

	// получаем подпись из массива параметров
	public static function doCall(string $url, string $method, array $params, int $user_id = 0):array {

		return self::_doCall($url, $method, $params, $user_id);
	}
}
