<?php

namespace Compass\FileBalancer;

/**
 * класс-интерфейс для работы с модулем world/conversation
 */
class Gateway_Socket_Conversation extends Gateway_Socket_Default {

	/**
	 * Запускает индексацию файла.
	 */
	public static function indexFile(string $file_map):void {

		[$status, $response] = self::doCall("file.index", [
			"file_map" => $file_map
		]);

		// если сокет-запрос не вернул ok
		if ($status != "ok") {

			$txt = toJson($response);
			throw new returnException("Socket request file.index status != ok. Response: {$txt}");
		}
	}

	/**
	 * Запускает переиндексацию файла.
	 */
	public static function reindexFile(string $file_map):void {

		[$status, $response] = self::doCall("file.reindex", [
			"file_map" => $file_map
		]);

		// если сокет-запрос не вернул ok
		if ($status != "ok") {

			$txt = toJson($response);
			throw new returnException("Socket request file.reindex status != ok. Response: {$txt}");
		}
	}

	// получаем подпись из массива параметров
	public static function doCall(string $method, array $params, int $user_id = 0):array {

		// получаем url и подпись
		$url = self::_getSocketCompanyUrl("conversation");
		return self::_doCall($url, $method, $params, $user_id);
	}
}
