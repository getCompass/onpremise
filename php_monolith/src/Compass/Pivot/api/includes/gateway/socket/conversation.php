<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\CompanyNotServedException;

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

	/**
	 * Отправить сообщение о звонке
	 *
	 * @param Struct_Db_PivotCompany_Company $space
	 * @param int                            $user_id
	 * @param string                         $conversation_map
	 * @param string                         $conference_id
	 * @param string                         $status
	 * @param string                         $link
	 *
	 * @return void
	 * @throws CompanyNotServedException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \cs_SocketRequestIsFailed
	 * @throws cs_CompanyIsHibernate
	 */
	public static function addMediaConferenceMessage(Struct_Db_PivotCompany_Company $space, int $user_id, string $conversation_map, string $conference_id, string $status, string $link):void {

		// формируем параметры для запроса
		$params = [
			"conversation_map" => $conversation_map,
			"conference_id"    => $conference_id,
			"status"           => $status,
			"link"             => $link,
		];

		[$status, $response] = self::_call(
			"conversations.addMediaConferenceMessage",
			$params,
			$user_id,
			$space->company_id,
			$space->domino_id,
			Domain_Company_Entity_Company::getPrivateKey($space->extra));
		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}
	}

	/**
	 * Проверяет возможность звонить
	 *
	 * @param Struct_Db_PivotCompany_Company $space
	 * @param int                            $user_id
	 * @param int                            $opponent_user_id
	 *
	 * @return string
	 * @throws CompanyNotServedException
	 * @throws Gateway_Socket_Exception_Conversation_ActionIsNotAllowed
	 * @throws Gateway_Socket_Exception_Conversation_InitiatorIsGuest
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \cs_SocketRequestIsFailed
	 * @throws cs_CompanyIsHibernate
	 */
	public static function checkIsAllowedForCall(Struct_Db_PivotCompany_Company $space, int $user_id, int $opponent_user_id):string {

		$params = ["user_id" => $user_id, "opponent_user_id" => $opponent_user_id, "method_version" => 2];

		[$status, $response] = self::_call(
			"conversations.checkIsAllowedForCall",
			$params, $user_id, $space->company_id,
			$space->domino_id,
			Domain_Company_Entity_Company::getPrivateKey($space->extra)
		);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw match ($response["error_code"]) {
				10021, 10022 => new Gateway_Socket_Exception_Conversation_ActionIsNotAllowed("action is not allowed"),
				10024, 10025, 10027 => new Gateway_Socket_Exception_Conversation_InitiatorIsGuest("initiator is guest"),
				default => throw new ReturnFatalException("unknown error code"),
			};
		}

		return $response["conversation_map"];
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
