<?php

namespace Compass\FileNode;

use BaseFrame\Exception\Request\ParamException;

/**
 * Методы для вызова для бота
 */
class Userbot_Files extends \BaseFrame\Controller\Api {

	// Поддерживаемые методы. Регистр не имеет значение */
	public const ALLOW_METHODS = [
		"upload",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * загружаем файл для бота
	 *
	 * @return array
	 * @throws ParamException
	 */
	public function upload():array {

		$token = $this->post(\Formatter::TYPE_STRING, "token");

		// получаем информацию из мемкэша по токену и проверяем что он валиден
		$cache   = Gateway_Memcache_Token::getDataByToken($token, Gateway_Memcache_Token::UPLOAD_TOKEN_TYPE);
		if ($cache === false) {
			return $this->error(1010, "malformed token");
		}

		$user_id = $cache["user_id"];

		// проверяем, что файл был успешно загружен
		if (!isset($_FILES["file"]) || $_FILES["file"]["error"] != UPLOAD_ERR_OK) {
			return $this->error(1010, "file was not uploaded");
		}

		// достаем оригинальное имя файла
		$uploaded_file_info = $_FILES["file"];
		$original_file_name = Type_File_Utils::getFileName($uploaded_file_info["name"]);

		// очищаем токен
		Gateway_Memcache_Token::removeToken($token);

		// сохраняем файл
		try {

			[$file_row] = Helper_File::uploadFile(
				$user_id, $cache["company_id"], $cache["company_url"], $cache["file_source"], $original_file_name, $uploaded_file_info["tmp_name"]
			);
		} catch (cs_InvalidFileTypeForSource) {
			return $this->error(1010, "file was not uploaded");
		}

		// подготовим сущность файл
		$prepared_file = Type_File_Utils::prepareFileForFormat($file_row, $user_id);

		return $this->ok([
			"file_id" => (string) $prepared_file["file_key"],
		]);
	}
}