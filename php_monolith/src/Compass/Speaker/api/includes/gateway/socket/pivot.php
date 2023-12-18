<?php

namespace Compass\Speaker;

/**
 * класс-интерфейс для работы с модулей pivot
 */
class Gateway_Socket_Pivot {

	// метод для получения активных звонков у пользователей
	public static function getBusyUsers(int $user_id, array $user_id_list):array {

		$params = [
			"user_id_list" => $user_id_list,
		];
		$response = self::_call("company.calls.getBusyUsers", $params, $user_id);

		if ($response["status"] != "ok") {

			if ($response["response"]["error_code"] == 423 || $response["response"]["error_code"] == 1) {
				throw new \returnException();
			}
			throw new \parseException("passed unknown error_code");
		}
		return $response["response"]["user_id_busy_call_list"];
	}

	// получаем последний звонок пользователя
	public static function getUserLastCall(int $user_id):Struct_Socket_Pivot_UserLastCall|false {

		$params = [
			"user_id" => $user_id,
		];
		$response = self::_call("company.calls.getUserLastCall", $params, $user_id);

		if ($response["status"] != "ok") {

			if ($response["response"]["error_code"] == 404) {
				return false;
			}
			throw new \parseException("passed unknown error_code");
		}

		$user_last_call = $response["response"]["user_last_call"];
		return new Struct_Socket_Pivot_UserLastCall(
			$user_last_call["user_id"],
			Type_Pack_Call::doDecrypt($user_last_call["call_key"]),
			$user_last_call["is_finished"]
		);
	}

	/**
	 * получаем все активные звонки
	 *
	 * @return Struct_Socket_Pivot_UserLastCall[]
	 *
	 * @throws \cs_DecryptHasFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getAllActiveCalls():array {

		$response = self::_call("company.calls.getAllCompanyActiveCalls", [], 0);

		if ($response["status"] != "ok") {

			if ($response["response"]["error_code"] == 423 || $response["response"]["error_code"] == 1) {
				throw new \returnException();
			}
			throw new \parseException("passed unknown error_code");
		}

		$output = [];
		foreach ($response["response"]["user_last_call_list"] as $user_last_call) {

			$output[] = new Struct_Socket_Pivot_UserLastCall(
				$user_last_call["user_id"],
				Type_Pack_Call::doDecrypt($user_last_call["call_key"]),
				$user_last_call["is_finished"]);
		}
		return $output;
	}

	// метод для установки статуса последнего звонка
	public static function setLastCall(array $user_id_list, string $call_map, int $is_finished = 0):bool {

		$params = [
			"user_id_list" => $user_id_list,
			"call_key"     => Type_Pack_Call::doEncrypt($call_map),
			"is_finished"  => $is_finished,
			"company_id"   => COMPANY_ID,
		];
		$response = self::_call("company.calls.setLastCall", $params, 0);

		if ($response["status"] != "ok") {

			if ($response["error_code"] == 423 || $response["error_code"] == 1) {
				throw new \returnException();
			}
			throw new \parseException("passed unknown error_code");
		}
		return true;
	}

	# region protected

	/**
	 * Получаем подпись из массива параметров.
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _call(string $method, array $params, int $user_id):array {

		// переводим в json параметры
		$json_params = toJson($params);

		// получаем url и подпись
		$url       = self::_getUrl();
		$signature = Type_Socket_Auth_Handler::getSignature(
			Type_Socket_Auth_Handler::AUTH_TYPE_SSL, COMPANY_TO_PIVOT_PRIVATE_KEY, $json_params
		);

		return Type_Socket_Main::doCall($url, $method, $json_params, $signature, $user_id);
	}

	/**
	 * получаем url
	 *
	 */
	protected static function _getUrl():string {

		$socket_url_config    = getConfig("SOCKET_URL");
		$socket_module_config = getConfig("SOCKET_MODULE");
		return $socket_url_config["pivot"] . $socket_module_config["pivot"]["socket_path"];
	}

	# endregion protected
}
