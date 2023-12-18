<?php

namespace Compass\FileNode;

/**
 * Класс описывает парсер файла с расширениями .doc, .docx
 */
class Type_File_Document_ContentParser_Word implements Type_File_Document_ContentParser_Interface {

	/**
	 * Получаем текстовое содержимое файла
	 *
	 * @return string
	 */
	public function parse(string $file_path):string {

		// создаем читателя
		$reader = self::_createReader($file_path);

		/** @var \PhpOffice\PhpWord\PhpWord $word_file читаем файл */
		$word_file = $reader->load($file_path);

		// в этот массив будем складывать все спаршенные текстовые данные
		$parsed_word_list = [];

		// пробегаемся по каждой секции документа и парсим
		$section_list = $word_file->getSections();
		foreach ($section_list as $section) {
			array_push($parsed_word_list, ...self::_parseTextDataFromSection($section));
		}

		return implode(" ", $parsed_word_list);
	}

	/**
	 * Создаем объект для чтения word файла
	 *
	 * @return \PhpOffice\PhpWord\Reader\AbstractReader
	 */
	protected static function _createReader(string $file_path):\PhpOffice\PhpWord\Reader\AbstractReader {

		// если это docx файл
		if (inHtml($file_path, "docx")) {
			return new \PhpOffice\PhpWord\Reader\Word2007();
		}

		// иначе
		return new \PhpOffice\PhpWord\Reader\MsDoc();
	}

	/**
	 * @return array
	 */
	protected static function _parseTextDataFromSection(\PhpOffice\PhpWord\Element\Section $section):array {

		// сюда сложим спаршенные строки
		$parsed_word_list = [];

		// парсим элементы контейнера
		array_push($parsed_word_list, ...self::_parseContainer($section));

		// парсим все заголовки секции
		$header_list = $section->getHeaders();
		foreach ($header_list as $header) {

			// каждый заголовок – это тоже контейнер, поэтому парсим его как и секцию
			array_push($parsed_word_list, ...self::_parseContainer($header));
		}

		// парсим все футеры секции
		$footer_list = $section->getFooters();
		foreach ($footer_list as $footer) {

			// каждый футер – это тоже контейнер, поэтому парсим его как и секцию
			array_push($parsed_word_list, ...self::_parseContainer($footer));
		}

		return $parsed_word_list;
	}

	/**
	 * Парсим элементы контейнера
	 *
	 * @return array
	 */
	protected static function _parseContainer(\PhpOffice\PhpWord\Element\AbstractContainer $container):array {

		// сюда сложим спаршенные строки
		$parsed_word_list = [];

		// получаем все элементы контейнера
		$element_list = $container->getElements();

		// пробегаемся по каждому элементу
		foreach ($element_list as $element) {

			array_push($parsed_word_list, ...self::_parseElement($element));
		}

		return $parsed_word_list;
	}

	/**
	 * Парсим текст в элементе
	 *
	 * @return array
	 */
	protected static function _parseElement(mixed $element):array {

		// сюда сложим спаршенные строки
		$parsed_word_list = [];

		// если это текстовый элемент
		if ($element instanceof \PhpOffice\PhpWord\Element\Text) {

			// получаем текст, обрабатываем и добавляем к ответу
			$raw_text           = $element->getText();
			$parsed_word_list[] = Type_File_Document_ContentParser_Helper::prepareText($raw_text);
		} elseif ($element instanceof \PhpOffice\PhpWord\Element\AbstractContainer) {

			// если это контейнер – парсим как контейнер
			array_push($parsed_word_list, ...self::_parseContainer($element));
		} elseif ($element instanceof \PhpOffice\PhpWord\Element\Table) {

			// если это таблица, то пробегаем по всем строчкам
			$row_list = $element->getRows();
			foreach ($row_list as $row) {

				// получаем все ячейки
				$cell_list = $row->getCells();
				foreach ($cell_list as $cell) {

					// ячейки – это контейнеры, парсим их через существующую функцию
					array_push($parsed_word_list, ...self::_parseContainer($cell));
				}
			}
		}

		return $parsed_word_list;
	}
}