<?php

namespace Compass\FileNode;

/**
 * Интерфейс, которому должен следовать любой класс для парсинга файлов
 */
interface Type_File_Document_ContentParser_Interface {

	/**
	 * Парсим содержимое файла
	 *
	 * @return string
	 */
	public function parse(string $file_path):string;
}