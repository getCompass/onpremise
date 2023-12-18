<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс-интерфейс для работы с модулем speaker
 */
class Gateway_Socket_Speaker extends Gateway_Socket_Default {

	/**
	 * Очистить все блокировки, если передан user_id чистим только по пользователю
	 *
	 * @throws \returnException
	 */
	public static function clearAllBlocks(int $user_id = 0):void {

		$params = [];
		if ($user_id > 0) {
			$params["user_id"] = $user_id;
		}

		[$status] = self::doCall("antispam.clearAll", $params);

		if ($status != "ok") {
			throw new ReturnFatalException(__METHOD__ . ": request to socket method antispam.clearAll is failed");
		}
	}

	/**
	 * Выполняет запрос на удаленной вызов скрипта обновления.
	 *
	 * @param string $script_name
	 * @param array  $script_data
	 * @param int    $flag_mask
	 *
	 * @return array
	 * @throws \returnException
	 *
	 * @noinspection DuplicatedCode
	 */
	public static function execCompanyUpdateScript(string $script_name, array $script_data, int $flag_mask):array {

		$params["script_data"] = $script_data;
		$params["script_name"] = $script_name;
		$params["flag_mask"]   = $flag_mask;

		// отправим запрос на удаление из списка
		[$status, $response] = self::doCall("system.execCompanyUpdateScript", $params);

		if ($status != "ok") {
			throw new ReturnFatalException($response["message"]);
		}

		return [$response["script_log"], $response["error_log"]];
	}

	/**
	 * Установить статус компании в конфиге
	 *
	 * @throws \returnException
	 */
	public static function setCompanyStatus(int $status):void {

		$method = "system.setCompanyStatus";

		[$status, $response] = self::doCall($method, ["status" => $status]);

		// если вернулась ошибка при удалении
		if ($status != "ok") {

			throw new ReturnFatalException(__METHOD__ . ": request to socket method {$method} is failed");
		}
	}
	// -------------------------------------------------------
	// UTILS
	// -------------------------------------------------------

	// получаем подпись из массива параметров
	public static function doCall(string $method, array $params, int $user_id = 0):array {

		// получаем url и подпись
		$url = self::_getSocketCompanyUrl("speaker");
		return self::_doCall($url, $method, $params, $user_id);
	}
}
