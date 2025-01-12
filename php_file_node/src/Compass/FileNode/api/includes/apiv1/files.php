<?php

namespace Compass\FileNode;

// для загрузки больших картинок
use BaseFrame\Exception\Request\ParamException;

ini_set("max_execution_time", 30);

/**
 * Группа методов для загрузки файлов
 */
class Apiv1_Files extends \BaseFrame\Controller\Api {

	// поддерживаемые методы. регистр не имеет значение
	public const ALLOW_METHODS = [
		"tryUpload",
		"tryPartialUpload",
		"doCropImage",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * загрузить файл по токену, полученному в php_file_balancer
	 *
	 * @return array
	 * @throws ParamException
	 */
	public function tryUpload():array {

		$tmp_file_path = $this->post(\Formatter::TYPE_STRING, "file_path");
		$file_name     = $this->post(\Formatter::TYPE_STRING, "file_name");
		$token         = $this->post(\Formatter::TYPE_STRING, "token");

		// получаем информацию из мемкэша по токену и проверяем что он валиден
		$cache     = Gateway_Memcache_Token::getDataByToken($token, Gateway_Memcache_Token::UPLOAD_TOKEN_TYPE);
		$user_id   = $this->_tryGetUserIdFromCacheForUpload($cache);
		$file_name = urldecode($file_name);

		// очищаем токен
		Gateway_Memcache_Token::removeToken($token);

		// сохраняем файл
		try {

			[$file_row, $download_token] = Helper_File::uploadFile(
				$user_id, $cache["company_id"], $cache["company_url"], $cache["file_source"], $file_name, $tmp_file_path
			);
		} catch (cs_InvalidFileTypeForSource $e) {
			throw new ParamException($e->getMessage());
		}

		// подготовим сущность файл
		$prepared_file = Type_File_Utils::prepareFileForFormat($file_row, $user_id, $download_token);

		return $this->ok([
			"file" => (object) Apiv1_Format::file($prepared_file),
		]);
	}

	/**
	 * Загрузить файл новым способом
	 */
	public function tryPartialUpload():array {

		$tmp_file_path = $this->post(\Formatter::TYPE_STRING, "file_path");
		$file_name     = $this->post(\Formatter::TYPE_STRING, "file_name");
		$sha1_hash     = $this->post(\Formatter::TYPE_STRING, "sha1_hash");
		$token         = $this->post(\Formatter::TYPE_STRING, "token");

		if (!file_exists($tmp_file_path)) {
			return $this->error(704, "File was not uploaded");
		}
		if (mb_strlen($file_name) < 1) {
			throw new ParamException("file name not found");
		}

		// получаем информацию из мемкэша по токену и проверяем что он валиден
		$cache     = Gateway_Memcache_Token::getDataByToken($token, Gateway_Memcache_Token::UPLOAD_TOKEN_TYPE);
		$user_id   = $this->_tryGetUserIdFromCacheForUpload($cache);
		$file_name = urldecode($file_name);

		// если хэш не совпал от отдаем 400
		$file_hash = Type_File_Utils::getFileHash($tmp_file_path);
		if ($file_hash != $sha1_hash) {
			throw new ParamException("file hash did not match");
		}

		// очищаем токен
		Gateway_Memcache_Token::removeToken($token);

		// сохраняем файл
		try {
			[$file_row, $download_token] = Helper_File::uploadFile($user_id, $cache["company_id"], $cache["company_url"], $cache["file_source"], $file_name, $tmp_file_path);
		} catch (cs_InvalidFileTypeForSource $e) {
			throw new ParamException($e->getMessage());
		}

		$prepared_file = Type_File_Utils::prepareFileForFormat($file_row, $user_id, $download_token);

		return $this->ok([
			"file" => (object) Apiv1_Format::file($prepared_file),
		]);
	}

	/**
	 * кропнуть файл по токену, полученному в php_file_balancer
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \ParseException
	 * @throws cs_DownloadFailed
	 * @long
	 */
	public function doCropImage():array {

		$token    = $this->post(\Formatter::TYPE_STRING, "token");
		$x_offset = (int) $this->post(\Formatter::TYPE_STRING, "x_offset");
		$y_offset = (int) $this->post(\Formatter::TYPE_STRING, "y_offset");
		$width    = (int) $this->post(\Formatter::TYPE_STRING, "width");
		$height   = (int) $this->post(\Formatter::TYPE_STRING, "height");

		if ($x_offset < 0 || $y_offset < 0 || $width < 1 || $height < 1) {
			throw new paramException("Passed negative offset or side");
		}

		// получаем информацию из мемкэша по токену и проверяем что он валиден
		$cache = Gateway_Memcache_Token::getDataByToken($token, Gateway_Memcache_Token::CROP_TOKEN_TYPE);
		if ($cache === false) {
			throw new paramException("Malformed token");
		}

		self::_checkImageSizes($cache, $width, $height, $x_offset, $y_offset);
		Gateway_Memcache_Token::removeToken($token);

		// пробуем скачать файл
		$node_id      = isset($cache["node_id"]) ? $cache["node_id"] : false;
		$file_content = Helper_File::downloadFile($cache["file_url"], true, node_id: $node_id);

		// кропаем файл и сохраняем файл на ноду
		$file_extension        = Type_File_Utils::getExtension($cache["file_name"]);
		$tmp_cropped_file_path = Type_File_Utils::generateTmpPath($file_extension);
		$tmp_file_path         = Type_File_Image_Process::doCropImage($file_content, $x_offset, $y_offset, $width, $height, $tmp_cropped_file_path);
		try {
			[$file_row, $download_token] = Helper_File::uploadFile($cache["user_id"], $cache["company_id"], $cache["company_url"], FILE_SOURCE_AVATAR, $cache["file_name"], $tmp_file_path, $cache["file_key"]);
		} catch (cs_InvalidFileTypeForSource $e) {
			throw new paramException($e->getMessage());
		}
		$prepared_file = Type_File_Utils::prepareFileForFormat($file_row, $cache["user_id"], $download_token);

		return $this->ok([
			"file" => (object) Apiv1_Format::file($prepared_file),
		]);
	}

	// проверяем размеры изображения, высоту и ширину
	protected static function _checkImageSizes(array $cache, int $width, int $height, int $x_offset, int $y_offset):void {

		// получаем данные файла из кеша
		$file_width  = $cache["file_width"];
		$file_height = $cache["file_height"];

		if ($file_width < $x_offset + $width || $file_height < $y_offset + $height) {
			throw new paramException("Passed wrong offset or side");
		}
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * пробуем получить user_id из кэша
	 *
	 * @param $cache
	 *
	 * @throws paramException
	 * @mixed - кэш моет быть false
	 */
	protected function _tryGetUserIdFromCacheForUpload($cache):int {

		if ($cache === false) {
			throw new paramException("Malformed token");
		}

		return $cache["user_id"];
	}
}