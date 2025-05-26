<?php

namespace Compass\FileBalancer;

/**
 * метод для работы с файлами
 */
class Socket_Files extends \BaseFrame\Controller\Socket {

	// поддерживаемые методы. Регистр не имеет значение */
	public const ALLOW_METHODS = [
		"trySaveFile",
		"doUpdateFile",
		"updateConfig",
		"getFile",
		"getFileByKeyList",
		"getNodeForUpload",
		"updateHash",
		"setDeleted",
		"getFileList",
		"getFileWithContentList",
		"setFileListDeleted",
		"getNodeForUserbot",
		"checkIsDeleted",
		"setContent",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * функция для получения информации по переданному списку файлов
	 *
	 */
	public function getFileList():array {

		$file_map_list = $this->post(\Formatter::TYPE_ARRAY, "file_map_list");

		// получаем информацию о файлах
		$file_list = Type_File_Main::getAll($file_map_list);

		// форматируем информацию о файлах
		$formatted_file_list = $this->_formatFileList($file_list);

		return $this->ok([
			"file_list" => (array) $formatted_file_list,
		]);
	}

	/**
	 * Возвращает данные файлы с содержимым (если оно есть).
	 */
	public function getFileWithContentList():array {

		$file_map_list = $this->post(\Formatter::TYPE_ARRAY, "file_map_list");

		// получаем информацию о файлах
		$file_list           = Type_File_Main::getAll($file_map_list);
		$formatted_file_list = [];

		foreach ($file_list as $item) {

			// получаем ссылку на ноду, где расположен файл
			$node_url = Type_Node_Config::getNodeUrl($item["node_id"]);

			// приводим файл к формату
			$prepared_file         = Type_File_Utils::prepareFileForFormat($item, $node_url, $this->user_id);
			$formatted_file_list[] = Apiv1_Format::fileWithContent($prepared_file);
		}

		return $this->ok([
			"file_with_content_list" => (array) $formatted_file_list,
		]);
	}

	/**
	 * Проверяем, удален ли файл
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \cs_DecryptHasFailed
	 */
	public function checkIsDeleted():array {

		$file_key = $this->post(\Formatter::TYPE_STRING, "file_key");
		$file_map = Type_Pack_File::tryDecrypt($file_key);

		// получаем информацию о файле
		$file = Type_File_Main::getOne($file_map);

		return $this->ok([
			"is_deleted" => (int) $file["is_deleted"],
		]);
	}

	/**
	 * форматируем список файлов
	 *
	 * @param array $file_list
	 *
	 * @return array
	 * @throws returnException
	 */
	protected function _formatFileList(array $file_list):array {

		$output = [];
		foreach ($file_list as $item) {

			// получаем ссылку на ноду, где расположен файл
			$node_url = Type_Node_Config::getNodeUrl($item["node_id"]);

			// приводим файл к формату
			$temp     = Type_File_Utils::prepareFileForFormat($item, $node_url, $this->user_id);
			$output[] = Apiv1_Format::file($temp);
		}

		return $output;
	}

	/**
	 * функция сохраняет файл
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws parseException
	 * @throws returnException
	 */
	public function trySaveFile():array {

		$file_type      = $this->post(\Formatter::TYPE_INT, "file_type");
		$node_id        = $this->post(\Formatter::TYPE_INT, "node_id");
		$size_kb        = $this->post(\Formatter::TYPE_INT, "size_kb");
		$mime_type      = $this->post(\Formatter::TYPE_STRING, "mime_type");
		$file_name      = $this->post(\Formatter::TYPE_STRING, "file_name");
		$file_extension = $this->post(\Formatter::TYPE_STRING, "file_extension");
		$extra          = $this->post(\Formatter::TYPE_ARRAY, "extra");
		$file_source    = $this->post(\Formatter::TYPE_INT, "file_source");
		$is_migrate     = $this->post(\Formatter::TYPE_INT, "is_migrate", 0);
		$file_hash      = $this->post(\Formatter::TYPE_STRING, "file_hash");
		$is_cdn         = $this->post(\Formatter::TYPE_INT, "is_cdn", 0);
		$is_cdn         = $is_cdn == 1 ? 1 : 0;

		$created_at = $is_migrate == 1 ? random_int(1527868800, time()) : time();

		[$file_row, $node_url, $download_token] = Domain_File_Action_TryUpload::run(
			$this->user_id, $file_type, $file_source, $node_id, $size_kb,
			$created_at, $mime_type, $file_name, $file_extension, $extra, $file_hash, $is_cdn
		);

		$file_row["file_key"] = Type_Pack_File::doEncrypt($file_row["file_map"]);

		return $this->ok([
			"file_row"       => (object) $file_row,
			"node_url"       => (string) $node_url,
			"download_token" => (string) $download_token,
		]);
	}

	/**
	 * функция обновляет информацию о файле и его расширении
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \cs_DecryptHasFailed
	 */
	public function doUpdateFile():array {

		$file_key       = $this->post(\Formatter::TYPE_STRING, "file_key");
		$extra          = $this->post(\Formatter::TYPE_ARRAY, "extra");
		$file_extension = $this->post(\Formatter::TYPE_STRING, "file_extension", "");
		$file_map       = Type_Pack_File::tryDecrypt($file_key);

		// формируем массив для обновления
		$set = [
			"extra"      => $extra,
			"updated_at" => time(),
		];

		if ($file_extension !== "") {
			$set["file_extension"] = $file_extension;
		}

		// обновляем запись с файлом
		Type_File_Main::set($file_map, $set);

		return $this->ok();
	}

	/**
	 * метод для получения информации о файле
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \cs_DecryptHasFailed
	 * @throws returnException
	 */
	public function getFile():array {

		$file_key = $this->post(\Formatter::TYPE_STRING, "file_key");
		$file_map = Type_Pack_File::tryDecrypt($file_key);

		// получаем информацию о файле
		$file_row = Type_File_Main::getOne($file_map);

		// получаем ссылку на ноду, где расположен файл
		$node_url = Type_Node_Config::getNodeUrl($file_row["node_id"]);

		return $this->ok([
			"file_row" => (object) $file_row,
			"node_url" => (string) $node_url,
		]);
	}

	/**
	 * метод для получения информации о файлах по их ключу
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \cs_DecryptHasFailed
	 * @throws parseException
	 * @throws returnException
	 */
	public function getFileByKeyList():array {

		$file_key_list = $this->post(\Formatter::TYPE_ARRAY, "file_key_list");

		$file_map_list = [];
		$output        = [];

		foreach ($file_key_list as $file_key) {
			$file_map_list[] = Type_Pack_File::tryDecrypt($file_key);
		}

		// получаем информацию о файле
		$file_list = Type_File_Main::getAll($file_map_list);

		$formatted_file_list = $this->_formatFileList($file_list);

		// возвращаем все с ключами
		foreach ($formatted_file_list as $file) {

			$file["file_key"] = Type_Pack_File::doEncrypt($file["file_map"]);
			unset($file["file_map"]);
			$output[] = $file;
		}
		return $this->ok([
			"file_list" => (array) $output,
		]);
	}

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
		$socket_url = Type_Node_Config::getSocketUrl($node_id);

		return $this->ok([
			"node_url"   => (string) $node_url,
			"socket_url" => (string) $socket_url,
		]);
	}

	/**
	 * функция обновляет поле  хэш у старых файлов
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \cs_DecryptHasFailed
	 */
	public function updateHash():array {

		$file_key  = $this->post("?s", "file_key");
		$file_hash = $this->post("?s", "file_hash");
		$file_map  = Type_Pack_File::tryDecrypt($file_key);

		// формируем массив для обновления
		$set = [
			"file_hash"  => $file_hash,
			"updated_at" => time(),
		];

		// обновляем запись с файлом
		Type_File_Main::set($file_map, $set);

		return $this->ok();
	}

	/**
	 * помечает файл удаленным
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \cs_DecryptHasFailed
	 * @throws returnException
	 */
	public function setDeleted():array {

		$node_id  = $this->post(\Formatter::TYPE_INT, "node_id");
		$file_key = $this->post(\Formatter::TYPE_STRING, "file_key");
		$file_map = Type_Pack_File::tryDecrypt($file_key);

		// получаем запись с файлом из базы
		$file_row = Type_File_Main::getOne($file_map);

		// проверяем, что запрос пришел с той ноды, на которой действительно лежит файл
		if ($file_row["node_id"] != $node_id) {
			throw new returnException("Someone trying to set file deleted from node, which is not file owner");
		}

		// помечаем файл в базе удаленным
		Type_File_Main::set($file_map, [
			"is_deleted" => 1,
			"updated_at" => time(),
		]);

		return $this->ok();
	}

	/**
	 * помеметить файлы удаленными
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws returnException
	 */
	public function setFileListDeleted():array {

		$file_map_list = $this->post(\Formatter::TYPE_ARRAY, "file_map_list");

		// удаляем каждый файл
		foreach ($file_map_list as $v) {

			// проверяем что запрос пришел на правильный сервер
			if (Type_Pack_File::getServerType($v) != CURRENT_SERVER) {
				throw new returnException("Someone trying to set file deleted from server, which is not from current server");
			}

			// помечаем файл как удаленный
			Type_File_Main::set($v, [
				"is_deleted" => 1,
				"updated_at" => time(),
			]);
		}

		return $this->ok();
	}

	/**
	 * получаем файловую ноду и токен для бота
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws returnException
	 */
	public function getNodeForUserbot():array {

		$userbot_user_id = $this->post(\Formatter::TYPE_INT, "userbot_user_id");

		[$node_url, $token] = Domain_File_Scenario_Socket::getNodeForUserbot($userbot_user_id);

		return $this->ok([
			"node_url"   => (string) $node_url,
			"file_token" => (string) $token,
		]);
	}

	/**
	 * Обновляем содержимое текстового документа в базе
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function setContent():array {

		$file_key = $this->post(\Formatter::TYPE_STRING, "file_key");
		$content  = $this->post(\Formatter::TYPE_STRING, "content");

		// расшифровываем ключ файла
		$file_map = Type_Pack_File::tryDecrypt($file_key);

		// удаляем эмодзи из контента
		$content = removeEmojiFromText($content);

		// обновляем содержимое, если что-то осталось
		if (mb_strlen($content) > 0) {
			Domain_File_Scenario_Socket::setContent($file_map, $content);
		}

		return $this->ok();
	}
}
