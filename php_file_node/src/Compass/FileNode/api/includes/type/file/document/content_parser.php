<?php

namespace Compass\FileNode;

/**
 * Класс для получения спаршенного содержимого файла
 */
class Type_File_Document_ContentParser {

	/**
	 * @var Type_File_Document_ContentParser_Interface как будем получать содержимое файла
	 */
	protected Type_File_Document_ContentParser_Interface $_strategy;

	public function __construct(string $file_extension) {

		$this->_strategy = match ($file_extension) {
			"doc", "docx" => new Type_File_Document_ContentParser_Word(),
			"pdf"         => new Type_File_Document_ContentParser_Pdf(),
			"xls", "xlsx" => new Type_File_Document_ContentParser_Excel(),
			default       => throw new \ParseException("unexpected file_extension ({$file_extension})"),
		};
	}

	/**
	 * Парсим содержимое документа
	 *
	 * @return string
	 */
	public function parse(string $file_path):string {

		return $this->_strategy->parse($file_path);
	}
}