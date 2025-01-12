<?php

namespace Compass\FileNode;

use BaseFrame\Exception\Request\ParamException;

/**
 * сокет методы для работы с файловой нодой
 */
class Socket_Nodes extends \BaseFrame\Controller\Socket {

	// поддерживаемые методы. Регистр не имеет значение */
	public const ALLOW_METHODS = [
		"trySaveToken",
		"addToRelocateQueue",
		"getFileTypeRel",
		"doCropImage",
		"trySaveTokenForCrop",
		"uploadDefaultFile",
		"replaceUserbotAvatar",
		"replacePreviewForWelcomeVideo",
		"uploadInvoice",
		"uploadAvatarFile",
		"uploadFileByUrl",
	];

	// функция сохраняет файл
	public function trySaveToken():array {

		$token       = $this->post("?s", "token");
		$file_source = $this->post("?i", "file_source");
		$company_id  = $this->post("?i", "company_id");
		$company_url = $this->post("?s", "company_url", "");

		// добавляем токен
		Gateway_Memcache_Token::addToken($token, $this->user_id, $file_source, $company_id, $company_url);

		return $this->ok();
	}

	// функция перемещает файл
	public function addToRelocateQueue():array {

		$file_key = $this->post("?s", "file_key");

		// добавляем в очередь для перемещения
		Gateway_Db_FileNode_Relocate::insert($file_key);

		return $this->ok();
	}

	// функция возвращает массив с соотношением mime_type и extension к типу файла
	public function getFileTypeRel():array {

		return $this->ok([
			"mime_type_rel"              => (array) Type_File_Main::getMimeTypeToFileTypeRel(),
			"extension_rel"              => (array) Type_File_Main::getExtensionToFileTypeRel(),
			"available_video_codec_list" => (array) Type_File_Video_Main::getAvailableCodecList(),
			"max_image_size_kb"          => (int) IMAGE_MAX_SIZE_KB,
			"max_image_side_px"          => (int) IMAGE_MAX_SIDE_PX,
		]);
	}

	// функция сохраняет токен для кропа
	public function trySaveTokenForCrop():array {

		$token       = $this->post("?s", "token");
		$file_key    = $this->post("?s", "file_key");
		$file_name   = $this->post("?s", "file_name");
		$file_url    = $this->post("?s", "file_url");
		$file_width  = $this->post("?i", "file_width");
		$file_height = $this->post("?i", "file_height");
		$company_id  = $this->post("?i", "company_id");
		$company_url = $this->post("?s", "company_url", "");

		// добавляем токен
		Gateway_Memcache_Token::addTokenForCrop($token, $this->user_id, $file_key, $file_url, $file_name, $file_width, $file_height, $company_id, $company_url);

		return $this->ok();
	}

	/**
	 * Загрузить дефолтный файл
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_InvalidFileTypeForSource
	 */
	public function uploadDefaultFile():array {

		$file_source = $this->post(\Formatter::TYPE_INT, "file_source");

		// проверяем, что файл был успешно загружен
		if (!isset($_FILES["file"]) || $_FILES["file"]["error"] != UPLOAD_ERR_OK) {
			return $this->error(704, "File was not uploaded");
		}

		// достаем оригинальное имя файла
		$uploaded_file_info = $_FILES["file"];
		$original_file_name = Type_File_Utils::getFileName($uploaded_file_info["name"]);

		// сохраняем файл
		[$file_row] = Helper_File::uploadFile(0, 0, "", $file_source, $original_file_name, $uploaded_file_info["tmp_name"], "", true);

		return $this->ok([
			"file_key" => (string) $file_row["file_key"],
		]);
	}

	/**
	 * Загружаем счет
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_InvalidFileTypeForSource
	 */
	public function uploadInvoice():array {

		$file_source = $this->post(\Formatter::TYPE_INT, "file_source");

		// проверяем, что файл был успешно загружен
		if (!isset($_FILES["file"]) || $_FILES["file"]["error"] != UPLOAD_ERR_OK) {
			return $this->error(704, "File was not uploaded");
		}

		// достаем оригинальное имя файла
		$uploaded_file_info = $_FILES["file"];
		$original_file_name = Type_File_Utils::getFileName($uploaded_file_info["name"]);

		// сохраняем файл
		[$file_row] = Helper_File::uploadFile(0, 0, "", $file_source, $original_file_name, $uploaded_file_info["tmp_name"]);

		return $this->ok([
			"file_key" => (string) $file_row["file_key"],
		]);
	}

	/**
	 * Загрузить файл аватарки
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws cs_InvalidFileTypeForSource
	 */
	public function uploadAvatarFile():array {

		$file_source = $this->post(\Formatter::TYPE_INT, "file_source");

		// проверяем, что файл был успешно загружен
		if (!isset($_FILES["file"]) || $_FILES["file"]["error"] != UPLOAD_ERR_OK) {
			return $this->error(704, "File was not uploaded");
		}

		//
		if ($file_source != FILE_SOURCE_AVATAR) {
			throw new \BaseFrame\Exception\Request\ParamException("incorrect file_source");
		}

		// достаем оригинальное имя файла
		$uploaded_file_info = $_FILES["file"];
		$original_file_name = Type_File_Utils::getFileName($uploaded_file_info["name"]);

		// сохраняем файл
		[$file_row] = Helper_File::uploadFile($this->user_id, 0, "", $file_source, $original_file_name, $uploaded_file_info["tmp_name"]);

		return $this->ok([
			"file_key" => (string) $file_row["file_key"],
		]);
	}

	/**
	 * Загрузить файл по file_url
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \ParseException
	 */
	public function uploadFileByUrl():array {

		$file_url    = $this->post(\Formatter::TYPE_STRING, "file_url");
		$file_source = $this->post(\Formatter::TYPE_INT, "file_source");
		$file_name   = $this->post(\Formatter::TYPE_STRING, "file_name");
		$company_id  = $this->post(\Formatter::TYPE_INT, "company_id");
		$company_url = $this->post(\Formatter::TYPE_STRING, "company_url");

		if (mb_strlen($file_name) > 255) {
			throw new \ParseException("File name `{$file_name}` overflow maximum length (255)");
		}

		// пробуем скачать файл
		try {
			$file_content = Helper_File::downloadFile($file_url, true);
		} catch (cs_DownloadFailed) {
			return $this->error(10020, "file download error");
		}

		// сохраняем содержимое скачиваемого файла во временный файл
		$tmp_file_path = Type_File_Utils::generateTmpPath();
		Type_File_Utils::saveContentToTmp($tmp_file_path, $file_content);

		// загружаем файл
		try {
			[$file_row] = Helper_File::uploadFile($this->user_id, $company_id, $company_url, $file_source, $file_name, $tmp_file_path);
		} catch (cs_InvalidFileTypeForSource) {
			return $this->error(10020, "file download error");
		}

		return $this->ok([
			"file_key" => (string) $file_row["file_key"],
		]);
	}

	/**
	 * заменяем аватарку пользовательского бота
	 *
	 * @return array
	 * @throws ParamException
	 */
	public function replaceUserbotAvatar():array {

		$file_key = $this->post(\Formatter::TYPE_STRING, "file_key");

		// проверяем, что файл был успешно загружен
		if (!isset($_FILES["file"]) || $_FILES["file"]["error"] != UPLOAD_ERR_OK) {
			return $this->error(704, "file was not uploaded");
		}

		// получаем новый загруженный файл
		$uploaded_file_info = $_FILES["file"];

		// получаем дефолт файл, которому заменим изображение
		$file_row = Gateway_Db_FileNode_File::getOne($file_key);

		// получаем все нарезанные файлы
		$image_size_list = Type_File_Image_Extra::getImageSizeListFromExtra($file_row["extra"]);
		foreach ($image_size_list as $size) {

			// заменяем нужный размер файла
			if ($size["width"] == 400 || $size["height"] == 400) {

				$file_path = Type_File_Utils::getFilePathFromPartPath($size["part_path"]);
				move_uploaded_file($uploaded_file_info["tmp_name"], $file_path);
				break;
			}
		}

		return $this->ok();
	}

	/**
	 * заменяем превью у видео-онбординга
	 */
	public function replacePreviewForWelcomeVideo():array {

		$welcome_video_file_key   = $this->post(\Formatter::TYPE_STRING, "welcome_video_file_key");
		$replace_preview_file_key = $this->post(\Formatter::TYPE_STRING, "replace_preview_file_key");

		try {
			Domain_File_Scenario_Socket::replacePreviewForWelcomeVideo($welcome_video_file_key, $replace_preview_file_key);
		} catch (Domain_File_Exception_FileNotFound) {
			throw new \BaseFrame\Exception\Request\ParamException("replace file not found");
		}

		return $this->ok();
	}
}