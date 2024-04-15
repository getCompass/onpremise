<?php

namespace Compass\FileBalancer;

/**
 * метод для работы с файлами
 */
class Integration_Files extends \BaseFrame\Controller\Integration {

	// поддерживаемые методы. Регистр не имеет значение */
	public const ALLOW_METHODS = [
		"getNodeForUpload",
	];

	/**
	 * метод для получения ноды для сохранения файла
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws paramException
	 * @throws returnException
	 */
	public function getNodeForUpload():array {

		$file_source = $this->post(\Formatter::TYPE_INT, "file_source");

		// получаем ноду для загрузки
		$node_id    = Type_Node_Config::getNodeIdForUpload($file_source);
		$node_url   = Type_Node_Config::getNodeUrl($node_id);

		return $this->ok([
			"node_url" => (string) $node_url,
		]);
	}
}