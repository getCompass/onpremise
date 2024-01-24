<?php

namespace Compass\FileBalancer;

/**
 * метод для работы с файлами
 */
class Socket_Previews extends \BaseFrame\Controller\Socket {

	// поддерживаемые методы. Регистр не имеет значение */
	public const ALLOW_METHODS = [
		"getNodeForDownload",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * метод возвращает ноду (url) выделенную для сохранения файла и токен
	 *
	 * @throws returnException|paramException
	 */
	public function getNodeForDownload():array {

		// получаем идентификатор ноды
		$node_id = Type_Node_Config::getNodeIdForUpload(FILE_SOURCE_MESSAGE_PREVIEW_IMAGE);

		// получаем нужную ноду
		$node_url   = Type_Node_Config::getNodeUrl($node_id);
		$socket_url = Type_Node_Config::getSocketUrl($node_id);

		return $this->ok([
			"node_url"   => (string) $node_url,
			"socket_url" => (string) $socket_url,
		]);
	}
}
