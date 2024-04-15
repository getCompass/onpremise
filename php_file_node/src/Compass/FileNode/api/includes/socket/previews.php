<?php

namespace Compass\FileNode;

use BaseFrame\Exception\Request\ParamException;

/**
 * Группа методов для URL превью
 */
class Socket_Previews extends \BaseFrame\Controller\Socket {

	// поддерживаемые методы. Регистр не имеет значение */
	public const ALLOW_METHODS = [
		"doImageDownload",
	];

	/**
	 * загрузить изображение по ссылке
	 *
	 * @return array
	 * @throws ParamException
	 */
	public function doImageDownload():array {

		$file_url    = $this->post("?s", "file_url");
		$company_id  = $this->post("?s", "company_id", 0);
		$company_url = $this->post("?s", "company_url", 0);

		try {
			$file_row = Domain_File_Action_DownloadFile::do($this->user_id, $file_url, $company_id, $company_url, FILE_SOURCE_MESSAGE_PREVIEW_IMAGE);
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