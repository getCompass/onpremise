<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

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

	/**
	 * Получаем информацию по пользователям
	 *
	 * @throws RowNotFoundException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws ReturnFatalException
	 */
	public static function getUserInfo(int $user_id):array {

		$ar_post = [
			"user_id" => $user_id,
		];
		$method  = "pivot.ldap.getUserInfo";
		[$status, $response] = self::_doCall(self::_getUrl(), $method, $ar_post, SOCKET_KEY_FEDERATION);

		if ($status != "ok") {

			if (isset($response["error_code"]) && $response["error_code"] == 1315001) {
				throw new RowNotFoundException("user not found");
			}
			throw new ReturnFatalException("passed unknown error_code");
		}

		return $response["user_info"];
	}

	/**
	 * Актуализируем данные пользователя
	 *
	 * @throws ReturnFatalException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function actualizeProfileData(int $user_id, array $ldap_account_data):void {

		// если передан аватар
		if (isset($ldap_account_data["avatar"]) && self::_isStringContainsBinary($ldap_account_data["avatar"])) {

			// если он в бинарном формате, конвертируем к base_64
			$ldap_account_data["avatar"] = base64_encode($ldap_account_data["avatar"]);
		}

		$ar_post = [
			"user_id"                            => (int) $user_id,
			"ldap_account_data"                  => (array) $ldap_account_data,
			"is_empty_attributes_update_enabled" => (int) Domain_Ldap_Entity_Config::isEmptyAttributesUpdateEnabled(),
		];

		$method = "pivot.ldap.actualizeProfileData";
		[$status, $response] = self::_doCall(self::_getUrl(), $method, $ar_post, SOCKET_KEY_FEDERATION);

		if ($status !== "ok") {
			throw new ReturnFatalException("unexpected response");
		}
	}

	/**
	 * Отправить код подтверждения
	 *
	 * @param string $mail
	 * @param string $confirm_code
	 * @param int    $template
	 *
	 * @return string
	 * @throws ReturnFatalException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function sendMailConfirmCode(string $mail, string $confirm_code, string $template):string {

		$ar_post = [
			"mail"         => $mail,
			"confirm_code" => $confirm_code,
			"template"     => $template,
		];

		$method = "pivot.ldap.sendMailConfirmCode";
		[$status, $response] = self::_doCall(self::_getUrl(), $method, $ar_post, SOCKET_KEY_FEDERATION);

		if ($status !== "ok") {
			throw new ReturnFatalException("unexpected response");
		}

		return $response["message_id"];
	}

	/**
	 * Если аватар пришел в binary формате
	 */
	protected static function _isStringContainsBinary($data):bool {

		return (
			is_string($data) &&
			!ctype_print($data) &&
			preg_match("/[^\x20-\x7E\t\r\n]/", $data)
		);
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
