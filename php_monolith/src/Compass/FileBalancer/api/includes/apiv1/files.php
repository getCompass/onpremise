<?php

namespace Compass\FileBalancer;

use CompassApp\Domain\Member\Entity\Permission;
use JetBrains\PhpStorm\ArrayShape;
use BaseFrame\Exception\Request\ParamException;

/**
 * группа методов для загрузки файлов
 */
class ApiV1_Files extends \BaseFrame\Controller\Api {

	// поддерживаемые методы. регистр не имеет значение
	public const ALLOW_METHODS = [
		"getInfoForUpload",
		"get",
		"doListen",
		"getFileTypeRel",
		"getInfoForCrop",
		"getBatching",
		"getAvatarBatching",
	];

	protected const _MAX_FILES_COUNT = 150;     // максимальное количество файлов в запросе

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * метод возвращает ноду (url) выделенную для сохранения файла и токен
	 */
	public function getInfoForUpload():array {

		$file_source = $this->post("?i", "file_source");

		// если на этом этапе передали служебный $file_source (88)
		$this->_throwIfPassedServiceFileSource($file_source);

		$this->_throwIfNotAllowedFileSource($file_source);

		// проверяем блокировку
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::FILES_GETINFOFORUPLOAD, "files");

		try {
			Domain_Member_Entity_Permission::check($this->user_id, $this->method_version, $file_source, Permission::IS_VOICE_MESSAGE_ENABLED);
		} catch (Domain_Member_Exception_ActionNotAllowed) {
			return $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		}

		// получаем рандомную ноду из доступных
		[$node_url, $socket_url] = $this->_getRandomNode($file_source);

		// генерируем токен и сохраняем на ноде
		$token = bin2hex(openssl_random_pseudo_bytes(20));
		$this->_trySaveTokenForUpload($socket_url, $token, $file_source);

		return $this->ok([
			"url"   => (string) $node_url,
			"token" => (string) $token,
		]);
	}

	/**
	 *
	 * выбрасываем ошибку, если передали не поддерживаемый file_source
	 *
	 * @param mixed $file_source
	 *
	 * @mixed - may be false
	 *
	 * @throws paramException
	 */
	protected function _throwIfNotAllowedFileSource(int $file_source):void {

		if (!in_array($file_source, Type_File_Main::ALLOWED_FILE_SOURCE_LIST) &&
			!in_array($file_source, Type_File_Main::ALLOWED_FILE_SOURCE_CDN_LIST)) {

			throw new ParamException(__METHOD__ . " file_source is not available");
		}
	}

	/**
	 *
	 * выбрасываем ошибку, если передали служебный file_source
	 *
	 * @param mixed $file_source
	 *
	 * @mixed - may be false
	 *
	 * @throws paramException
	 */
	protected function _throwIfPassedServiceFileSource(int $file_source):void {

		// если на этом этапе передали служебный $file_source (88)
		if ($file_source == FILE_SOURCE_MESSAGE_ANY) {

			Type_System_Admin::log("service_file_source", [
				"user_id" => $this->user_id,
				"ip"      => getIp(),
				"ua"      => getUa(),
			], true);
			throw new paramException(__METHOD__ . " - got service file_source by client");
		}
	}

	// сохраняем токен на ноде
	protected function _trySaveTokenForUpload(string $node_url, string $token, int $file_source):void {

		// отправляем сокет запрос на ноду для записи токена
		[$status,] = Gateway_Socket_FileNode::doCall($node_url . "/api/socket/", "nodes.trySaveToken", [
			"token"              => $token,
			"file_source"        => $file_source,
			"company_id"         => CURRENT_SERVER == CLOUD_SERVER ? COMPANY_ID : 0,
			"company_url"        => CURRENT_SERVER == CLOUD_SERVER ? self::_getCompanyUrl() : "",
		], $this->user_id);

		// если не ок — бросаем экшепшен
		if ($status != "ok") {
			throw new \returnException("Unhandled error_code from socket call in " . __METHOD__);
		}
	}

	/**
	 * метод для получения ноды для кропа картинки
	 */
	public function getInfoForCrop():array {

		$file_key = $this->post("?s", "file_key");
		$file_map = Type_Pack_File::tryDecrypt($file_key);

		// проверяем что запрос пришел на нужный тип сервера
		$this->_throwIfPassedFileMapFromAnotherServerType($file_map);
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::FILES_GETINFOCROPIMAGE, "files");

		// разрешаем кропать только аватарки
		if (Type_Pack_File::getFileSource($file_map) != FILE_SOURCE_AVATAR) {

			if (Type_System_Legacy::isGetInfoForCropV2()) {
				return $this->error(5101001, "File source not support crop");
			}

			throw new paramException("File source not support crop");
		}

		// разрешаем кропать только картинки
		if (Type_Pack_File::getFileType($file_map) != FILE_TYPE_IMAGE) {

			if (Type_System_Legacy::isGetInfoForCropV2()) {
				return $this->error(5101002, "File type not support crop");
			}

			throw new paramException("File type not support crop");
		}

		// получаем запись с файлом из базы
		$file_row = Type_File_Main::getOne($file_map);
		if ($file_row["is_deleted"] == 1) {
			return $this->error(571, "File was deleted");
		}

		$file_source = Type_Pack_File::getFileSource($file_map);
		[$node_url, $socket_url] = $this->_getRandomNode($file_source);

		$file_url    = Type_File_Utils::getUrlByPartPath($node_url, $file_row["extra"]["original_part_path"]);
		$file_width  = $file_row["extra"]["original_image_item"]["width"];
		$file_height = $file_row["extra"]["original_image_item"]["height"];

		// генерируем токен и сохраняем на ноде
		$token = bin2hex(openssl_random_pseudo_bytes(20));
		$this->_trySaveTokenForCrop($socket_url, $token, $file_map, $file_url, $file_row["file_name"], $file_width, $file_height);

		return $this->ok([
			"url"   => (string) $node_url,
			"token" => (string) $token,
		]);
	}

	// сохраняем токен на ноде
	protected function _trySaveTokenForCrop(string $node_url, string $token, string $file_map, string $file_url, string $file_name, int $file_width, int $file_height):void {

		// отправляем сокет запрос на ноду для записи токена
		[$status,] = Gateway_Socket_FileNode::doCall($node_url . "/api/socket/", "nodes.trySaveTokenForCrop", [
			"token"              => $token,
			"file_key"           => Type_Pack_File::doEncrypt($file_map),
			"file_url"           => $file_url,
			"file_name"          => $file_name,
			"file_width"         => $file_width,
			"file_height"        => $file_height,
			"company_id"         => CURRENT_SERVER == CLOUD_SERVER ? COMPANY_ID : 0,
			"company_url"        => CURRENT_SERVER == CLOUD_SERVER ? self::_getCompanyUrl() : "",
		], $this->user_id);

		// если не ок — бросаем экзепшен
		if ($status != "ok") {
			throw new \returnException("Unhandled error_code from socket call in " . __METHOD__);
		}
	}

	/**
	 * метод для получение файла по его key
	 */
	public function get():array {

		$file_key = $this->post("?s", "file_key");
		$file_map = Type_Pack_File::tryDecrypt($file_key);

		// проверяем что запрос пришел на нужный dpc
		$this->_throwIfPassedFileMapFromAnotherServerType($file_map);

		// получаем запись с файлом из базы
		$file_row = Type_File_Main::getOne($file_map);

		// проверяем, не удален ли файл
		if ($file_row["is_deleted"] == 1) {
			return $this->error(571, "File was deleted");
		}

		// получаем ссылку на ноду, где расположен файл
		$node_url = Type_Node_Config::getNodeUrl($file_row["node_id"]);

		// подготовим файл к форматированию
		$temp = Type_File_Utils::prepareFileForFormat($file_row, $node_url, $this->user_id);

		return $this->ok([
			"file" => (object) Apiv1_Format::file($temp),
		]);
	}

	/**
	 * метод для получение списка файлов по их ключам
	 */
	public function getBatching():array {

		$file_key_list = $this->post("?a", "file_key_list");

		// оставляем только уникальные значения в массиве
		$file_key_list = array_unique($file_key_list);

		$this->_throwIfFileListIsIncorrect($file_key_list);
		$file_map_list = $this->_doDecryptFileKeyList($file_key_list);

		// получаем информацию о файлах
		$file_list = Type_File_Main::getAll($file_map_list);

		$not_deleted_file_list = [];
		$deleted_file_map_list = [];
		foreach ($file_list as $item) {

			if ($item["is_deleted"] == 1) {

				$deleted_file_map_list[] = $item["file_map"];
				continue;
			}
			$not_deleted_file_list[] = $item;
		}

		// приводим массив не удаленных файлов к формату
		$not_deleted_file_list = $this->_formatFileList($not_deleted_file_list);

		$output = $this->_makeGetBatchingOutput($not_deleted_file_list, $deleted_file_map_list);
		return $this->ok($output);
	}

	// формируем ответ для метода files.getBatching
	#[ArrayShape(["file_list" => "array", "deleted_file_key_list" => "array"])]
	protected function _makeGetBatchingOutput(array $not_deleted_file_list, array $deleted_file_map_list):array {

		$deleted_file_key_list = [];

		// если массив удаленных файлов не пустой
		if (count($deleted_file_map_list) > 0) {

			foreach ($deleted_file_map_list as $item) {
				$deleted_file_key_list[] = Type_Pack_File::doEncrypt($item);
			}
		}

		return [
			"file_list"             => (array) $not_deleted_file_list,
			"deleted_file_key_list" => (array) $deleted_file_key_list,
		];
	}

	/**
	 * прослушать голосовое сообщение
	 */
	public function doListen():array {

		$file_key = $this->post(\Formatter::TYPE_STRING, "file_key");
		$file_map = Type_Pack_File::tryDecrypt($file_key);

		// проверяем что запрос пришел на нужный сервер
		$this->_throwIfPassedFileMapFromAnotherServerType($file_map);

		// выбрасываем ошибку, если файл недоступен для прослушивания
		if (!Type_File_Voice::isAllowToListen($file_map)) {
			throw new paramException("File type is not voice");
		}

		// прослушиваем голосовое сообщение
		Type_File_Voice::doListenVoiceFile($file_map, $this->user_id);

		// отправляем ws-ивент
		Gateway_Bus_Sender::doFileVoiceListen($this->user_id, $file_map);

		return $this->ok();
	}

	/**
	 * метод возвращает тип файла по его расширению и mime_type
	 */
	public function getFileTypeRel():array {

		// получаем рандомную ноду из доступных
		[, $socket_url] = $this->_getRandomNode(FILE_SOURCE_MESSAGE_DEFAULT);

		// отправляем сокет запрос для получения типа файла
		[$status, $response] = Gateway_Socket_FileNode::doCall($socket_url . "/api/socket/", "nodes.getFileTypeRel", [], $this->user_id);

		// если не ок — бросаем экзепшен
		if ($status != "ok") {
			throw new \returnException("Unhandled error_code from socket call in " . __METHOD__);
		}

		return $this->ok([
			"mime_type_rel"              => (object) Apiv1_Format::fileRel($response["mime_type_rel"]),
			"extension_rel"              => (object) Apiv1_Format::fileRel($response["extension_rel"]),
			"available_video_codec_list" => (array) $response["available_video_codec_list"],
			"max_image_size_kb"          => (int) $response["max_image_size_kb"],
			"max_image_side_px"          => (int) $response["max_image_side_px"],
		]);
	}

	/**
	 * метод для получение списка аватаров по их ключам
	 */
	public function getAvatarBatching():array {

		$file_key_list = $this->post(\Formatter::TYPE_ARRAY, "file_key_list");

		Gateway_Bus_CollectorAgent::init()->inc("row0");

		// оставляем только уникальные значения в массиве
		$file_key_list = array_unique($file_key_list);

		$this->_throwIfFileListIsIncorrect($file_key_list);
		$file_map_list = $this->_doDecryptFileKeyList($file_key_list, true);

		// получаем аватарки
		$file_list = Type_File_Main::getAll($file_map_list);

		$not_deleted_file_list = [];
		$deleted_file_key_list = [];
		foreach ($file_list as $item) {

			if ($item["is_deleted"] == 1) {

				$deleted_file_key_list[] = Type_Pack_File::doEncrypt($item["file_map"]);
				continue;
			}
			$not_deleted_file_list[] = $item;
		}

		// приводим массив к формату frontend
		$formatted_file_list = $this->_formatFileList($not_deleted_file_list);

		return $this->ok([
			"file_list"             => (array) $formatted_file_list,
			"deleted_file_key_list" => (array) $deleted_file_key_list,
		]);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * получаем url любой ноды для загрузки
	 *
	 * @throws paramException
	 * @throws returnException
	 */
	protected function _getRandomNode(int $file_source):array {

		// получаем id ноды для загрузки
		$node_id = Type_Node_Config::getNodeIdForUpload($file_source);

		// получаем url ноды
		return [Type_Node_Config::getNodeUrl($node_id), Type_Node_Config::getSocketUrl($node_id)];
	}

	// выбрасываем ошибку, если пришел файл с другого типа сервера
	protected function _throwIfPassedFileMapFromAnotherServerType(string $file_map):void {

		// получаем dpc файла
		$server_type = Type_Pack_File::getServerType($file_map);

		// если dpc файла не совпал с текущим
		if ($server_type != CURRENT_SERVER) {
			throw new paramException("The method is requested on a wrong server_type, " . __METHOD__);
		}
	}

	// выбрасываем ошибку, если список файлов некорректный
	protected function _throwIfFileListIsIncorrect(array $file_list):void {

		// если пришел пустой массив файлов
		if (count($file_list) < 1) {
			throw new paramException("passed empty file_list");
		}

		// если пришел слишком большой массив
		if (count($file_list) > self::_MAX_FILES_COUNT) {
			throw new paramException("passed file_list biggest than max");
		}
	}

	// преобразуем пришедшие ключи в map
	protected function _doDecryptFileKeyList(array $file_list, bool $is_avatar = false):array {

		$file_map_list = [];
		foreach ($file_list as $item) {

			// преобразуем key в map
			$file_map = Type_Pack_File::tryDecrypt($item);

			// выбрасывем ошибку, если файл с другого dpc
			$this->_throwIfPassedFileMapFromAnotherServerType($file_map);

			// если все файлы в массиве должны быть аватарками
			if ($is_avatar) {
				$this->_throwIfPassedNotAvatarFileKey($file_map);
			}
			$file_map_list[] = $file_map;
		}
		return $file_map_list;
	}

	// бросаем ошибку, если файл не с перманент ноды
	protected function _throwIfPassedNotAvatarFileKey(string $file_map):void {

		// проверяем file_source
		if (!in_array(Type_Pack_File::getFileSource($file_map), [FILE_SOURCE_AVATAR, FILE_SOURCE_AVATAR_DEFAULT, FILE_SOURCE_AVATAR_CDN])) {
			throw new paramException("passed not avatar file_key");
		}

		// проверяем, что файл является изображением
		if (Type_Pack_File::getFileType($file_map) != FILE_TYPE_IMAGE) {
			throw new paramException("passed not avatar file_key");
		}
	}

	// форматируем список файлов
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
	 * получаем url
	 *
	 */
	protected static function _getCompanyUrl():string {

		$socket_url_config = getConfig("SOCKET_URL");
		return $socket_url_config["company"];
	}
}