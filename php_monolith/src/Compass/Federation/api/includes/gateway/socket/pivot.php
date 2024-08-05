<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс-интерфейс для общения с pivot.
 */
class Gateway_Socket_Pivot extends Gateway_Socket_Default {

	/**
	 * блокируем пользователю возможность аутентифицироваться в приложении
	 *
	 * @throws ReturnFatalException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function blockUserAuthentication(string $user_id):void {

		$ar_post = [
			"user_id" => $user_id,
		];
		$method  = "pivot.ldap.blockUserAuthentication";
		[$status, $response] = self::_doCall(self::_getUrl(), $method, $ar_post, SOCKET_KEY_FEDERATION);

		if ($status !== "ok") {
			throw new ReturnFatalException("unexpected response");
		}
	}

	/**
	 * исключаем пользователя из всех команд
	 *
	 * @throws ReturnFatalException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function kickUserFromAllCompanies(string $user_id):void {

		$ar_post = [
			"user_id" => $user_id,
		];
		$method  = "pivot.ldap.kickUserFromAllCompanies";
		[$status, $response] = self::_doCall(self::_getUrl(), $method, $ar_post, SOCKET_KEY_FEDERATION);

		if ($status !== "ok") {
			throw new ReturnFatalException("unexpected response");
		}
	}

	/**
	 * разблокируем пользователю возможность аутентифицироваться в приложении
	 *
	 * @throws ReturnFatalException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function unblockUserAuthentication(int $user_id):void {

		$ar_post = [
			"user_id" => $user_id,
		];
		$method  = "pivot.ldap.unblockUserAuthentication";
		[$status, $response] = self::_doCall(self::_getUrl(), $method, $ar_post, SOCKET_KEY_FEDERATION);

		if ($status !== "ok") {
			throw new ReturnFatalException("unexpected response");
		}
	}

	/**
	 * включена ли возможность авторизации через ldap
	 *
	 * @return bool
	 * @throws \parseException
	 * @throws \returnException
	 * @throws ReturnFatalException
	 */
	public static function isLdapAuthAvailable():bool {

		$ar_post = [];
		$method  = "pivot.ldap.isLdapAuthAvailable";
		[$status, $response] = self::_doCall(self::_getUrl(), $method, $ar_post, SOCKET_KEY_FEDERATION);

		if ($status !== "ok" || !isset($response["is_available"])) {
			throw new ReturnFatalException("unexpected response");
		}

		return boolval($response["is_available"]);
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

		return $socket_url_config["pivot"] . $socket_module_config["pivot"]["socket_path"];
	}
}
