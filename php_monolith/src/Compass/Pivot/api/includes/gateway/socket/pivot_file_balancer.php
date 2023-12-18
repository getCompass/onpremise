<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс-интерфейс для общения с pivot php_balancer
 */
class Gateway_Socket_PivotFileBalancer extends Gateway_Socket_Default {

	/**
	 * Получаем информацию о списке файлов по file_map_list
	 *
	 * @param array $file_map_list
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 */
	public static function getFileList(array $file_map_list):array {

		$ar_post = [
			"file_map_list" => $file_map_list,
		];
		[$status, $response] = self::_doCall(self::_getUrl(), "files.getFileList", $ar_post);

		if ($status !== "ok") {
			throw new ReturnFatalException("unexpected response");
		}

		if (!isset($response["file_list"])) {
			throw new ParseFatalException("unexpected response");
		}

		return $response["file_list"];
	}

	/**
	 * Узнаем, удален ли файл
	 *
	 * @param string $file_key
	 *
	 * @return int
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 */
	public static function checkIsDeleted(string $file_key):int {

		$ar_post = [
			"file_key" => $file_key,
		];
		[$status, $response] = self::_doCall(self::_getUrl(), "files.checkIsDeleted", $ar_post);

		if ($status !== "ok") {
			throw new ReturnFatalException("unexpected response");
		}

		if (!isset($response["is_deleted"])) {
			throw new ParseFatalException("unexpected response");
		}

		return (int) $response["is_deleted"];
	}

	/**
	 * Получаем ноду для загрузки файла
	 *
	 * @param int $file_source
	 *
	 * @return string
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 */
	public static function getNodeForUpload(int $file_source):string {

		$ar_post = [
			"file_source" => $file_source,
		];

		[$status, $response] = self::_doCall(self::_getUrl(), "files.getNodeForUpload", $ar_post);

		if ($status !== "ok") {
			throw new ReturnFatalException("unexpected response");
		}

		if (!isset($response["socket_url"])) {
			throw new ParseFatalException("unexpected response");
		}

		return $response["socket_url"];
	}

	/**
	 * Очистить все блокировки, если передан пользователь чистим только по пользователю
	 *
	 * @param int $user_id
	 *
	 * @throws ReturnFatalException
	 */
	public static function clearAllBlocks(int $user_id = 0):void {

		$params = [];
		if ($user_id > 0) {
			$params["user_id"] = $user_id;
		}

		[$status] = self::_doCall(self::_getUrl(), "antispam.clearAll", $params);

		if ($status != "ok") {
			throw new ReturnFatalException(__METHOD__ . ": request to socket method antispam.clearAll is failed");
		}
	}

	/**
	 * помечаем файлы удаленными
	 *
	 * @param array $file_map_list
	 *
	 * @throws ReturnFatalException
	 */
	public static function deleteFiles(array $file_map_list):void {

		$ar_post = ["file_map_list" => $file_map_list];
		[$status] = self::_doCall(self::_getUrl(), "files.setFileListDeleted", $ar_post);

		if ($status != "ok") {
			throw new ReturnFatalException(__METHOD__ . ": request to socket method antispam.clearAll is failed");
		}
	}

	/**
	 * Получаем файлы по ключу
	 *
	 * @throws ReturnFatalException
	 */
	public static function getFileByKeyList(array $file_key_list):array {

		$ar_post = ["file_key_list" => $file_key_list];
		[$status, $response] = self::_doCall(self::_getUrl(), "files.getFileByKeyList", $ar_post);

		if ($status != "ok") {
			throw new ReturnFatalException(__METHOD__ . ": request to socket method files.getFileByKeyList is failed");
		}

		return $response["file_list"];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Получаем ссылку на модуль
	 *
	 */
	protected static function _getUrl():string {

		return self::_getSocketFilesUrl();
	}
}