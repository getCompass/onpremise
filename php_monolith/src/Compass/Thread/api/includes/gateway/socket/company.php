<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-интерфейс для работы с модулем company
 */
class Gateway_Socket_Company extends Gateway_Socket_Default {

	/**
	 * получаем список объектов родительской сущности треда из заявки батчинг-методом
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getRequestDataBatching(array $request_id_list_by_type, int $user_id):array {

		[$status, $response] = self::doCall("hiring.hiringrequest.getRequestDataBatching", [
			"request_id_list_by_type" => $request_id_list_by_type,
		], $user_id);

		if ($status != "ok") {
			throw new ReturnFatalException(__METHOD__ . ": socket hiring.hiringrequest.getRequestDataBatching fail");
		}

		if (!isset($response["request_list_by_type"], $response["not_allowed_id_list_by_type"])) {
			throw new ParseFatalException(__METHOD__ . ": socket without expected response");
		}
		$users_by_type = $response["users_by_type"];

		$request_list_by_type = [];
		$users                = [];
		foreach ($response["request_list_by_type"] as $parent_name_type => $request_list) {

			switch ($parent_name_type) {

				case "hiring_request":
					$request_list_by_type[PARENT_ENTITY_TYPE_HIRING_REQUEST] = $request_list;
					$users[PARENT_ENTITY_TYPE_HIRING_REQUEST]                = $users_by_type[$parent_name_type];
					break;

				case "dismissal_request":
					$request_list_by_type[PARENT_ENTITY_TYPE_DISMISSAL_REQUEST] = $request_list;
					$users[PARENT_ENTITY_TYPE_DISMISSAL_REQUEST]                = $users_by_type[$parent_name_type];
			}
		}

		return [$request_list_by_type, $response["not_allowed_id_list_by_type"], $users];
	}

	/**
	 * закрепляем thread_map за заявкой найма/увольнения
	 *
	 * @throws \returnException
	 */
	public static function setThreadMapForHireRequest(int $request_id, string $request_name_type, string $thread_map):void {

		[$status] = self::doCall("hiring.hiringrequest.setThreadMap", [
			"request_id"        => $request_id,
			"request_name_type" => $request_name_type,
			"thread_map"        => $thread_map,
		]);

		if ($status != "ok") {
			throw new ReturnFatalException(__METHOD__ . ": socket hiringrequest.setThreadMap fail");
		}
	}

	/**
	 * получаем данные для создания треда у заявки
	 *
	 * @throws \returnException
	 */
	public static function getMetaForCreateThread(int $user_id, int $request_id, string $request_type):array {

		// запрос к php_conversation, спрашиваем можно ли прикрепить тред к этому сообщению
		[$status, $response] = self::doCall("hiring.hiringrequest.getMetaForCreateThread", [
			"request_id"        => $request_id,
			"request_name_type" => $request_type,
		], $user_id);

		// выбрасываем ошибку, если запрос не был успешным
		if ($status != "ok") {
			throw new ReturnFatalException(__METHOD__ . ": socket hiringrequest.getMetaForCreateThread fail");
		}

		return $response;
	}

	/**
	 * Существует ли такая компания
	 *
	 * @throws \returnException
	 */
	public static function exists():bool {

		[$status, $response] = self::doCall("company.main.exists", []);

		// если сокет-запрос не вернул ok
		if ($status != "ok") {
			$txt = toJson($response);
			throw new ReturnFatalException("Socket request member.getUserRoleList status != ok. Response: {$txt}");
		}

		return $response["exists"];
	}

	/**
	 * Получить информацию о пользовател по id
	 *
	 * @param int $user_id
	 *
	 * @return array
	 * @throws \returnException
	 */
	public static function getInfoUser(int $user_id):array {

		[$status, $response] = self::doCall("company.member.getUserInfo", [
			"user_id" => $user_id,
		]);

		// если сокет-запрос не вернул ok
		if ($status != "ok") {

			$txt = toJson($response);
			throw new ReturnFatalException("Socket request member.getUserInfo status != ok. Response: {$txt}");
		}

		return $response["user_info"];
	}

	/**
	 * Получить значение конфига компании по ключу
	 *
	 * @param string $key
	 *
	 * @return array
	 * @throws \returnException
	 */
	public static function getConfigByKey(string $key):array {

		[$status, $response] = self::doCall("company.config.getConfigByKey", [
			"key" => $key,
		]);

		// если сокет-запрос не вернул ok
		if ($status != "ok") {

			$txt = toJson($response);
			throw new ReturnFatalException("Socket request member.getUserRoleList status != ok. Response: {$txt}");
		}

		return $response["config"];
	}

	/**
	 * вызываем метод сокет-запросом
	 *
	 */
	public static function doCall(string $method, array $params, int $user_id = 0):array {

		// получаем url и подпись
		$url = self::_getSocketCompanyUrl("company");
		return self::_doCall($url, $method, $params, $user_id);
	}
}
