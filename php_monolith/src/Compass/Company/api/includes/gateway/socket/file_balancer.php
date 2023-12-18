<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс-интерфейс для работы с модулем file_balancer
 */
class Gateway_Socket_FileBalancer extends Gateway_Socket_Default {

	/**
	 * очистить все блокировки, если передан user_id чистим только по пользователю
	 *
	 * @throws \returnException
	 */
	public static function clearAllBlocks(int $user_id = 0):void {

		$params = [];
		if ($user_id > 0) {
			$params["user_id"] = $user_id;
		}

		[$status] = self::doCall("antispam.clearAll", COMPANY_ID, $params);

		if ($status != "ok") {
			throw new ReturnFatalException(__METHOD__ . ": request to socket method antispam.clearAll is failed");
		}
	}

	/**
	 * Установить статус компании в конфиге
	 *
	 * @throws \returnException
	 */
	public static function setCompanyStatus(int $status):void {

		$method = "system.setCompanyStatus";

		[$status, $response] = self::doCall($method, COMPANY_ID, ["status" => $status]);

		// если вернулась ошибка при удалении
		if ($status != "ok") {

			throw new ReturnFatalException(__METHOD__ . ": request to socket method {$method} is failed");
		}
	}

	/**
	 * получаем файлы
	 *
	 * @throws \returnException
	 */
	public static function getFiles(array $file_key_list, bool $is_pivot = false):array {

		$ar_post = ["file_key_list" => $file_key_list];

		$company_id = $is_pivot ? 0 : COMPANY_ID;
		[$status, $response] = self::doCall("files.getFileByKeyList", $company_id, $ar_post);

		if ($status != "ok") {
			throw new ReturnFatalException(__METHOD__ . ": request to socket method files.getFileByKeyList is failed");
		}

		return $response["file_list"];
	}

	// -------------------------------------------------------
	// UTILS
	// -------------------------------------------------------

	// получаем подпись из массива параметров
	public static function doCall(string $method, int $company_id, array $params, int $user_id = 0):array {

		// получаем url и подпись
		if ($company_id > 0) {
			$url = self::_getSocketCompanyUrl("files");
		} else {
			$url = self::_getSocketPivotUrl("files");
		}

		return self::_doCall($url, $method, $params, $user_id);
	}
}
