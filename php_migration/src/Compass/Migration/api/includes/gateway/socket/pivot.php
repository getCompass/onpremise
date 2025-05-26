<?php

namespace Compass\Migration;

/**
 * класс-интерфейс для работы с модулей pivot
 */
class Gateway_Socket_Pivot {

	/**
	 * Получаем список компаний
	 *
	 * @param int $count
	 * @param int $offset
	 *
	 * @return array
	 * @throws \returnException
	 */
	public static function getCompanyIdList(int $count, int $offset):array {

		$ar_post = [
			"count"  => $count,
			"offset" => $offset,
		];

		// делаем запрос
		[$status, $response] = self::_call("system.getActiveCompanyIdList", $ar_post, 0);
		if ($status != "ok") {
			throw new \returnException("status socket request != ok");
		}
		return $response["company_id_list"];
	}

	/**
	 * Залочить компанию для миграции
	 *
	 * @param int  $company_id
	 * @param bool $need_port
	 *
	 * @return void
	 * @throws Gateway_Socket_Exception_CompanyIsBusy
	 * @throws \returnException
	 */
	public static function lockBeforeMigration(int $company_id, bool $need_port):void {

		$ar_post = [
			"company_id" => $company_id,
			"domino_id"  => DOMINO_ID,
			"need_port"  => (int) $need_port
		];

		[$status, $response] = self::_call("system.lockBeforeMigration", $ar_post, 0);
		if ($status != "ok") {

			if (isset($response["error_code"])) {

				throw match ($response["error_code"]) {
					1407001 => new Gateway_Socket_Exception_CompanyIsBusy("company is busy")
				};
			}
			throw new \returnException("status socket request != ok");
		}
	}

	/**
	 * Разлочить компанию после миграции
	 *
	 * @param int  $company_id
	 * @param bool $is_service_port
	 *
	 * @return void
	 * @throws \returnException
	 */
	public static function unlockAfterMigration(int $company_id, bool $is_service_port):void {

		$ar_post = [
			"company_id" => $company_id,
			"domino_id"  => DOMINO_ID,
			"is_service_port"  => (int) $is_service_port
		];

		[$status, $response] = self::_call("system.unlockAfterMigration", $ar_post, 0);
		if ($status != "ok") {
			throw new \returnException("status socket request != ok");
		}
	}

	/**
	 * Получить информацию о процессе накатки миграций на домино
	 *
	 * @return array
	 * @throws \returnException
	 */
	public static function getDominoMigrationOptions():array {

		$ar_post = [
			"domino_id" => DOMINO_ID,
		];

		[$status, $response] = self::_call("system.getDominoMigrationOptions", $ar_post, 0);
		if ($status != "ok") {

			throw new \returnException("status socket request != ok");
		}

		return [
			"need_backup" => $response["need_backup"],
			"need_wakeup" => $response["need_wakeup"],
		];
	}

	/**
	 * Получить информацию о процессе накатки миграций на домино
	 *
	 * @param int $company_id
	 *
	 * @return void
	 * @throws \returnException
	 */
	public static function bindServicePort(int $company_id):void {

		$ar_post = [
			"domino_id"  => DOMINO_ID,
			"company_id" => $company_id,
		];

		[$status, $_] = self::_call("domino.system.bindServicePort", $ar_post, 0);
		if ($status != "ok") {

			throw new \returnException("status socket request != ok");
		}
	}

	/**
	 * Получаем список компаний на домино
	 *
	 * @param string $domino_id
	 *
	 * @return array
	 * @throws \returnException
	 */
	public static function getActiveDominoCompanyIdList(string $domino_id):array {

		$ar_post = [
			"domino_id" => $domino_id,
		];

		// делаем запрос
		[$status, $response] = self::_call("system.getActiveDominoCompanyIdList", $ar_post, 0);
		if ($status != "ok") {
			throw new \returnException("status socket request != ok");
		}
		return $response["company_id_list"];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Получаем подпись из массива параметров.
	 *
	 * @param string $method
	 * @param array  $params
	 * @param int    $user_id
	 *
	 * @return array
	 */
	protected static function _call(string $method, array $params, int $user_id):array {

		// переводим в json параметры
		$json_params = toJson($params);

		// получаем url и подпись
		$url       = self::_getUrl();
		$signature = Type_Socket_Auth_Handler::getSignature(
			Type_Socket_Auth_Handler::AUTH_TYPE_KEY, SOCKET_KEY_MIGRATION, $json_params
		);

		return Type_Socket_Main::doCall($url, $method, $json_params, $signature, $user_id);
	}

	/**
	 * получаем url
	 *
	 * @return string
	 */
	protected static function _getUrl():string {

		$socket_url_config    = getConfig("SOCKET_URL");
		$socket_module_config = getConfig("SOCKET_MODULE");
		return $socket_url_config["pivot"] . $socket_module_config["pivot"]["socket_path"];
	}
}
