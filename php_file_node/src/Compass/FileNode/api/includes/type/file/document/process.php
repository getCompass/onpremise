<?php

namespace Compass\FileNode;

/**
 * Класс для обработки документов
 */
class Type_File_Document_Process {

	/**
	 * делает первичную обработку документа, возвращает extra
	 *
	 * @param        $company_id
	 */
	public static function doProcessOnUpload(string $part_path, int $company_id, string $company_url):array {

		return Type_File_Document_Extra::getExtra($part_path, $company_id, $company_url);
	}
}