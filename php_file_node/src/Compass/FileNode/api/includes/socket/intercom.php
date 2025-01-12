<?php

namespace Compass\FileNode;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Server\ServerProvider;

/**
 * группа сокет методов предназначена для работы с модулем php_intercom
 */
class Socket_Intercom extends \BaseFrame\Controller\Socket {

	public const ALLOW_METHODS = [
		"doUploadFile",
	];

	/**
	 * Переопределяем родительский work
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Request\EndpointAccessDeniedException
	 */
	public function work(string $method_name, int $method_version, array $post_data, int $user_id, array $extra):array {

		// действия с intercom не доступны на on-premise окружении
		if (ServerProvider::isOnPremise()) {
			throw new ParseFatalException("action is not allowed on this environment");
		}

		return parent::work($method_name, $method_version, $post_data, $user_id, $extra);
	}

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * скачать и загрузить на ноду
	 * @return array
	 * @throws ParamException
	 * @throws \ParseException
	 * @throws cs_InvalidFileTypeForSource
	 */
	public function doUploadFile() {

		$file_url    = $this->post("?s", "file_url");
		$file_name   = $this->post("?s", "file_name");
		$company_id  = $this->post("?i", "company_id");
		$company_url = $this->post("?s", "company_url");

		if (mb_strlen($file_name) > 255) {
			throw new \ParseException("File name `{$file_name}` overflow maximum length (255)");
		}

		// пробуем скачать файл
		try {
			$file_content = Helper_File::downloadFile($file_url, true, 10,
				false, Helper_File::MAX_FILE_DOWNLOAD_CONTENT_LENGTH, INTERCOM_PROXY);
		} catch (cs_DownloadFailed) {
			return $this->error(10020, "file download error");
		}

		// сохраняем содержимое скачиваемого файла во временный файл
		$tmp_file_path = Type_File_Utils::generateTmpPath();
		Type_File_Utils::saveContentToTmp($tmp_file_path, $file_content);

		// определяем file_source файла
		$file_source = self::_doIdentifyFileSource($tmp_file_path);

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
	 * Определяем file_source
	 *
	 * @param string $tmp_file_path
	 *
	 * @return int
	 * @throws cs_InvalidFileTypeForSource
	 */
	protected static function _doIdentifyFileSource(string $tmp_file_path):int {

		// получаем extension
		$file_source    = FILE_SOURCE_MESSAGE_DEFAULT;
		$mime_type      = Type_File_Utils::getMimeType($tmp_file_path);
		$size_kb        = Type_File_Utils::getFileSizeKb($tmp_file_path);
		$file_extension = Type_File_Utils::getExtension($tmp_file_path);
		$file_type      = Type_File_Main::getFileType($tmp_file_path, $mime_type, $size_kb, $file_source, $file_extension);
		return Type_File_Main::tryGetFileSource($file_type, $file_source);
	}
}