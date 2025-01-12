<?php

namespace Compass\FileNode;

use BaseFrame\Exception\Request\ParamException;

/**
 * группа сокет методов предназначена для получения статуса файловой ноды php_file_node
 */
class Socket_System extends \BaseFrame\Controller\Socket {

	// Поддерживаемые методы. Регистр не имеет значение */
	public const ALLOW_METHODS = [
		"tryPing",
		"doUploadFile",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * Метод в большей степени предназначен для получения статуса доступности ноды
	 * а так же получения информации о ней (тип ноды, code_uniq идентификатор)
	 *
	 * @return array
	 */
	public function tryPing() {

		return $this->ok([
			"code_uniq" => (int) CODE_UNIQ_VERSION,
		]);
	}

	/**
	 * скачать и загрузить на ноду
	 * @return array
	 * @throws ParamException
	 * @throws \ParseException
	 */
	public function doUploadFile() {

		$file_url    = $this->post("?s", "file_url");
		$file_name   = $this->post("?s", "file_name");
		$company_id  = $this->post("?i", "company_id");
		$company_url = $this->post("?s", "company_url");

		if (mb_strlen($file_name) > 255) {
			throw new \ParseException("File name `{$file_name}` overflow maximum length (255)");
		}

		// определяем file_source файла
		$file_source = self::_doIdentifyFileSource($file_name);

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

	// определяем file_source на основе расширения файла (png/pdf)
	protected static function _doIdentifyFileSource(string $file_name):int {

		// получаем extension
		$file_extension = Type_File_Utils::getExtension($file_name);

		return match ($file_extension) {
			"jpg", "png", "jpeg" => FILE_SOURCE_MESSAGE_IMAGE,
			"pdf"                => FILE_SOURCE_MESSAGE_DOCUMENT,
			default              => throw new paramException("Unsupported file extension `{$file_extension}`"),
		};
	}
}