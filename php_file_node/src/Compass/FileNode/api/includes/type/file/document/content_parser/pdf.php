<?php

namespace Compass\FileNode;

/**
 * Класс описывает парсер файла с расширением .pdf
 */
class Type_File_Document_ContentParser_Pdf  implements Type_File_Document_ContentParser_Interface  {

	/**
	 * Получаем текстовое содержимое файла
	 *
	 * @return string
	 */
	public function parse(string $file_path):string {

		// создаём объект конфига
		$config = new \Smalot\PdfParser\Config();

		// будем игнорировать все изображения в содержимом
		$config->setRetainImageContent(false);

		/**
		 * пустая строка может предотвратить разделение слов (баг когда слова в тексте разбиваются на слоги)
		 * @see https://github.com/smalot/pdfparser/blob/master/doc/CustomConfig.md
		 */
		$config->setHorizontalOffset("");

		// создаем объект парсера
		$parser = new \Smalot\PdfParser\Parser([], $config);

		// парсим файл
		try {
			$pdf_file = $parser->parseFile($file_path);
		} catch (\Exception) {

			/**
			 * можем поймать здесь exception если файл зашифрован на запись
			 * и фактически он может быть прочтен
			 * известный баг либы
			 * @see https://github.com/smalot/pdfparser/issues/488*
			 * возвращаем пустую строку в таком случае – здесь ничего не поделать:
			 */
			return "";
		}

		// возвращаем обработанный текст
		return Type_File_Document_ContentParser_Helper::prepareText($pdf_file->getText());
	}
}