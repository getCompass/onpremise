<?php

namespace Compass\FileBalancer;

/**
 * Сценарии для домена файла
 */
class Domain_File_Scenario_Socket {

	/**
	 * получаем ноду для пользовательского бота
	 */
	public static function getNodeForUserbot(int $userbot_user_id):array {

		$node_id    = Type_Node_Config::getNodeIdForUpload(FILE_SOURCE_MESSAGE_DEFAULT);
		$node_url   = Type_Node_Config::getNodeUrl($node_id);
		$socket_url = Type_Node_Config::getSocketUrl($node_id);

		// генерируем токен и сохраняем на ноде
		$token = bin2hex(openssl_random_pseudo_bytes(20));

		// отправляем сокет запрос на ноду для записи токена
		[$status,] = Gateway_Socket_FileNode::doCall($socket_url . "/api/socket/", "nodes.trySaveToken", [
			"token"       => $token,
			"file_source" => FILE_SOURCE_MESSAGE_DEFAULT,
			"company_id"  => COMPANY_ID,
			"company_url" => self::_getCompanyUrl(),
		], $userbot_user_id);

		// если не ок — бросаем экшепшен
		if ($status != "ok") {
			throw new \returnException("Unhandled error_code from socket call in " . __METHOD__);
		}

		return [$node_url, $token];
	}

	/**
	 * получаем url
	 *
	 */
	protected static function _getCompanyUrl():string {

		$socket_url_config = getConfig("SOCKET_URL");
		return $socket_url_config["company"];
	}

	/**
	 * Устанавливаем новое содержимое для файла
	 */
	public static function setContent(string $file_map, string $content):void {

		Domain_File_Action_SetContent::do($file_map, $content);
	}
}