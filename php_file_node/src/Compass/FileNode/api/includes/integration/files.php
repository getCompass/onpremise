<?php

namespace Compass\FileNode;

use BaseFrame\Exception\Request\ParamException;

/**
 * метод для работы с файлами
 */
class Integration_Files extends \BaseFrame\Controller\Integration {

	// поддерживаемые методы. Регистр не имеет значение */
	public const ALLOW_METHODS = [
		"downloadByLink",
	];

	/**
	 * загрузить файл по ссылке
	 *
	 * @return array
	 * @throws ParamException
	 */
	public function downloadByLink():array {

		$file_url    = $this->post("?s", "file_url");
		$file_source = $this->post("?i", "file_source");
		$user_id     = $this->post("?i", "user_id", 0);

		try {
			$file_row = Domain_File_Action_DownloadFile::do($user_id, $file_url, 0, "", $file_source);
		} catch (Domain_File_Exception_IncorrectFileName) {
			return $this->error(10020, "file download error");
		} catch (cs_DownloadFailed|\cs_CurlError) {
			return $this->error(10020, "file download error");
		} catch (cs_InvalidFileTypeForSource) {
			return $this->error(10020, "file download error");
		}

		return $this->ok([
			"file_key" => (string) $file_row["file_key"],
		]);
	}
}