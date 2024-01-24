<?php

namespace Compass\FileBalancer;

/**
 * контроллер для всего связанного с релокейтом файлов
 */
class Socket_Relocate extends \BaseFrame\Controller\Socket {

	// поддерживаемые методы. Регистр не имеет значение */
	public const ALLOW_METHODS = [
		"setDeleted",
		"updateProcessedFile",
		"addToRandomNodeRelocateQueue",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * метод для добавления файла в очередь на перемещение на другую ноду
	 *
	 * @return array
	 *
	 * @throws parseException
	 * @throws returnException|paramException
	 */
	public function addToRandomNodeRelocateQueue() {

		$file_map = $this->post("?s", "file_map");
		$node_id  = $this->post("?i", "node_id");

		// проверяем, что эта нода была отключена на запись
		if (Type_Node_Config::isNodeEnabledForNew($node_id)) {

			throw new parseException(__METHOD__ . ": someone trying to relocate NODE {$node_id}, but forgot to update config (expect that enabled_for_new = 0)");
		}

		// получаем url ноды для релокейта
		$file_source = Type_Pack_File::getFileSource($file_map);
		$node_id     = Type_Node_Config::getNodeIdForUpload($file_source);
		$socket_url  = Type_Node_Config::getSocketUrl($node_id);

		// делаем сокет запрос nodes.doRelocate
		[$status,] = Gateway_Socket_FileNode::doCall($socket_url . "/api/socket/", "nodes.addToRelocateQueue", [
			"file_map" => $file_map,
		]);

		// если не ок — бросаем экзепшен
		if ($status != "ok") {
			throw new returnException(__METHOD__ . ": node returns not ok in socket call nodes.addToRelocateQueue");
		}

		return $this->ok();
	}

	/**
	 * помечает файл удаленным
	 *
	 * @return array
	 *
	 * @throws parseException
	 * @throws returnException
	 */
	public function setDeleted() {

		$file_map = $this->post("?s", "file_map");
		$node_id  = $this->post("?i", "node_id");

		// получаем запись с файлом из базы
		$file_row = Type_File_Main::getOne($file_map);

		// проверяем, что запрос пришел с той ноды, на которой действительно лежит файл
		if ($file_row["node_id"] != $node_id) {
			throw new returnException("Someone trying to set file deleted from node, which is not file owner");
		}

		// помечаем файл в базе удаленным
		Type_File_Main::set($file_map, [
			"is_deleted" => 1,
		]);

		return $this->ok();
	}

	/**
	 * метод для смены ноды и обновления поля extra
	 * после обработки файла нодой, принимающей его у себя
	 *
	 * @throws parseException|paramException
	 */
	public function updateProcessedFile():array {

		$file_map = $this->post("?s", "file_map");
		$node_id  = $this->post("?i", "node_id");
		$extra    = $this->post("?a", "extra");

		// обновляем запись с файлом
		Type_File_Main::set($file_map, [
			"node_id"    => $node_id,
			"extra"      => $extra,
			"updated_at" => time(),
		]);

		// получаем обновленную запись
		$file_row = Type_File_Main::getOne($file_map);

		return $this->ok([
			"file_row" => (object) $file_row,
		]);
	}
}
