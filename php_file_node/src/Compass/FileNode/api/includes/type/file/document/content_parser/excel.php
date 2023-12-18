<?php

namespace Compass\FileNode;

/**
 * Класс описывает парсер файла с расширениями .xls, .xlsx
 */
class Type_File_Document_ContentParser_Excel implements Type_File_Document_ContentParser_Interface {

	/**
	 * Получаем текстовое содержимое файла
	 *
	 * @return string
	 */
	public function parse(string $file_path):string {

		$reader = self::_createReader($file_path);
		$reader->setReadDataOnly(true);

		// получаем список
		$worksheet_list = $reader->listWorksheetInfo($file_path);

		// в этот массив будем складывать все спаршенные текстовые данные
		$parsed_word_list = [];

		// пробегаемся по каждому листу
		foreach ($worksheet_list as $worksheet) {

			// название листа
			$worksheet_name = $worksheet["worksheetName"];

			// складываем его в список спаршенных данных
			$parsed_word_list[] = $worksheet_name;

			// работаем с листом
			$reader->setLoadSheetsOnly($worksheet_name);

			// подгружаем содержимое листа
			$spreadsheet    = $reader->load($file_path);
			$worksheet_data = $spreadsheet->getActiveSheet();

			// проходимся по всему содержимому листа
			array_push($parsed_word_list, ...self::_parseTextData($worksheet_data->toArray()));
		}

		return implode(" ", $parsed_word_list);
	}

	/**
	 * Создаем объект для чтения excel файла
	 *
	 * @return \PhpOffice\PhpSpreadsheet\Reader\BaseReader
	 */
	protected static function _createReader(string $file_path):\PhpOffice\PhpSpreadsheet\Reader\BaseReader {

		// если это xlsx файл
		if (inHtml($file_path, "xlsx")) {
			return new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
		}

		// иначе
		return new \PhpOffice\PhpSpreadsheet\Reader\Xls();
	}

	/**
	 * Получаем все текстовые данные из листа таблицы, возвращаем все в формате одномерного массива со строками
	 *
	 * @return string[]
	 */
	protected static function _parseTextData(array $worksheet_data):array {

		// сюда сложим спаршенные строки
		$parsed_word_list = [];

		// бежимся по строкам листа
		foreach ($worksheet_data as $element_data) {

			// если это массив, то рекурсивно парсим содержимое каждой строки/ячейки
			if (is_array($element_data)) {
				array_push($parsed_word_list, ...self::_parseTextData($element_data));
			}

			// если это строка, то добавляем обработанное содержимое к ответу
			if (is_string($element_data)) {
				$parsed_word_list[] = Type_File_Document_ContentParser_Helper::prepareText($element_data);
			}
		}

		return $parsed_word_list;
	}
}