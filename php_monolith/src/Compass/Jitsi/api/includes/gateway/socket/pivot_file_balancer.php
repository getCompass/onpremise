<?php

namespace Compass\Jitsi;

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
	 * @throws ReturnFatalException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws ParseFatalException
	 */
	public static function getFileList(array $file_map_list):array {

		$ar_post = [
			"file_map_list" => $file_map_list,
		];

		/** @noinspection PhpUnusedLocalVariableInspection */
		[$status, $response, $response_code] = self::_doCall(self::_getUrl(), "files.getFileList", $ar_post, SOCKET_KEY_JITSI);

		if ($status !== "ok") {
			throw new ReturnFatalException("unexpected response");
		}

		if (!isset($response["file_list"])) {
			throw new ParseFatalException("unexpected response");
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

		$socket_url_config    = getConfig("SOCKET_URL");
		$socket_module_config = getConfig("SOCKET_MODULE");
		return $socket_url_config["pivot"] . $socket_module_config["files"]["socket_path"];
	}
}