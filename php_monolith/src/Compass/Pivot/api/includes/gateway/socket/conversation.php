<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * задача класса общаться между php_pivot -> php_world(conversation)
 */
class Gateway_Socket_Conversation {

	/**
	 * Получаем ключ чата службы поддержки
	 *
	 * @param int    $user_id
	 * @param int    $space_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return string
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 */
	public static function getUserSupportConversationKey(int $user_id, int $space_id, string $domino_id, string $private_key):string {

		// формируем параметры для запроса
		$params = [
			"user_id" => (int) $user_id,
		];
		[$status, $response] = self::_call("intercom.getUserSupportConversationKey", $params, $user_id, $space_id, $domino_id, $private_key);
		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}
		return $response["support_conversation_key"];
	}

	/**
	 * Создаем чат "Спасибо"
	 *
	 * @param int    $creator_user_id
	 * @param string $respect_conversation_avatar_file_key
	 * @param bool   $is_force_update
	 * @param int    $space_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return int
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 */
	public static function createRespectConversation(int $creator_user_id, string $respect_conversation_avatar_file_key, bool $is_force_update, int $space_id, string $domino_id, string $private_key):int {

		// формируем параметры для запроса
		$params = [
			"creator_user_id"                      => $creator_user_id,
			"respect_conversation_avatar_file_key" => $respect_conversation_avatar_file_key,
			"is_force_update"                      => (int) $is_force_update,
		];
		[$status, $response] = self::_call("groups.createRespectConversation", $params, 0, $space_id, $domino_id, $private_key);
		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}

		return $response["is_created"];
	}

	/**
	 * Добавляем участников пространства в чат "Спасибо"
	 *
	 * @param int    $space_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return void
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 */
	public static function addMembersToRespectConversation(int $space_id, string $domino_id, string $private_key):void {

		// формируем параметры для запроса
		$params = [];
		[$status, $response] = self::_call("groups.addMembersToRespectConversation", $params, 0, $space_id, $domino_id, $private_key);
		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * делаем вызов в php_world(conversation)
	 *
	 * @param string $method
	 * @param array  $params
	 * @param int    $user_id
	 * @param int    $space_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 */
	protected static function _call(string $method, array $params, int $user_id, int $space_id, string $domino_id, string $private_key):array {

		// переводим в json параметры
		$json_params = toJson($params);

		// получаем url и подпись
		$url = self::_getEntrypoint($domino_id);

		$signature = Type_Socket_Auth_Handler::getSignature(Type_Socket_Auth_Handler::AUTH_TYPE_SSL, $private_key, $json_params);
		try {
			return Type_Socket_Main::doCall($url, $method, $json_params, $signature, $space_id, $user_id);
		} catch (\cs_SocketRequestIsFailed $e) {

			if ($e->getHttpStatusCode() == 404) {
				throw new Gateway_Socket_Exception_CompanyIsNotServed("space is not served");
			}
			if ($e->getHttpStatusCode() == 503) {
				throw new cs_CompanyIsHibernate("space is hibernated");
			}
			throw $e;
		}
	}

	/**
	 * получаем url
	 *
	 * @param string $domino
	 *
	 * @return string
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 */
	protected static function _getEntryPoint(string $domino):string {

		$domino_entrypoint_config = getConfig("DOMINO_ENTRYPOINT");
		$socket_module_config     = getConfig("SOCKET_MODULE");

		if (!isset($domino_entrypoint_config[$domino])) {
			throw new \BaseFrame\Exception\Request\CompanyNotServedException("company not served");
		}
		return $domino_entrypoint_config[$domino]["private_entrypoint"] . $socket_module_config["conversation"]["socket_path"];
	}
}
