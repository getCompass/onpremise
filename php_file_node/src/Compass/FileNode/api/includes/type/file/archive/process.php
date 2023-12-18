<?php

namespace Compass\FileNode;

/**
 * Класс для работы с файлами-архивами (любая первичная и последующая обработка)
 */
class Type_File_Archive_Process {

	// делает первичную обработку архива, возвращает extra
	public static function doProcessOnUpload(string $part_path, int $company_id, string $company_url):array {

		return Type_File_Document_Extra::getExtra($part_path, $company_id, $company_url);
	}
}