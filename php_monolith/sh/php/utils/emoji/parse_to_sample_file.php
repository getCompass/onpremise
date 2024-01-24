<?php declare(strict_types = 1);

require_once __DIR__ . "/../../../../start.php";

/**
 * скрипт для того, чтобы txt файлы с эмодзи превратить в нормальный json
 */
class Utils_Emoji_Parse_To_Sample_File {

	// путь до файла с кривым исполнением
	protected const _INVALID_EMOJI_LIST_FILE_PATH = "invalid_emoji_file";

	// путь до файла, куда сохраним красивый json
	protected const _EMOJI_KEYWORDS_CONFIG_FILE_PATH = "replace.json";

	/**
	 * достаем последовательности
	 * @long
	 */
	public static function doWork(string $lang):void {

		// получаем массив эмодзи с ключевыми словами
		$emoji_json = self::_parseInvalidEmojiToJson($lang);

		$output_emoji_keywords_list = self::_convertArrayToSampleJson($emoji_json);

		// пишем конфиг в файл
		self::_writeKeywordsToFile($output_emoji_keywords_list);
	}

	/**
	 * Парсим кривой файл в JSON
	 *
	 * @param string $lang
	 *
	 * @return array
	 */
	protected static function _parseInvalidEmojiToJson(string $lang):array {

		$output_text = "";

		// получаем содержимое файла-инвалида
		$file       = file(self::_INVALID_EMOJI_LIST_FILE_PATH . "_" . $lang . ".txt");
		$line_count = count($file);

		// построчно парсим файл
		for ($i = 0; $i < $line_count; $i++) {

			$input_line = $file[$i];

			// если это первая или последняя строка - не трогаем
			if (in_array($i, [0, $line_count - 1])) {

				$output_text .= $input_line;
				continue;
			}

			// избавляемся от всякой ненужной шелухи в строки
			$output_line = str_replace("\r", "", $input_line);
			$output_line = str_replace("\n", "", $output_line);
			$output_line = str_replace("/", " ", $output_line);

			// добавляем в конец строки запятую
			if ($i != $line_count - 2) {
				$output_line = $output_line . ",";
			}

			// меняем стрелку на двоеточие, как в json
			$output_line = str_replace("=>", ":", $output_line);
			$output_text .= $output_line . PHP_EOL;
		}

		// получившаяся строка должна быть JSONом
		$output_json = fromJson($output_text);

		// формируем итоговый массив с эмодзи, получая список ключевых слов
		$result_array = [];
		foreach ($output_json as $key => $item) {
			$result_array[$key] = explode("|", $item);
		}

		return $result_array;
	}

	/**
	 * Формируем json файл формата сэмпла
	 *
	 * @param array $input_emoji_keywords_list
	 *
	 * @return array
	 */
	protected static function _convertArrayToSampleJson(array $input_emoji_keywords_list):array {

		$output_emoji_keywords_list = [];

		foreach ($input_emoji_keywords_list as $k => $v) {

			foreach ($v as &$item) {
				$item = trim($item);
			}

			$output_emoji_keywords_list[] = [
				"emoji"    => $k,
				"keywords" => $v,
			];
		}

		return $output_emoji_keywords_list;
	}

	/**
	 * сохраняем в файл
	 */
	protected static function _writeKeywordsToFile(array $emoji_keywords_list):void {

		@file_put_contents(self::_EMOJI_KEYWORDS_CONFIG_FILE_PATH, json_encode($emoji_keywords_list, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
	}
}

console("Введите lang (ru, en, de, fr, it, es)");
$lang = (trim(readline()));
if (mb_strlen($lang) < 1) {

	console("Передан неверный {$lang}");
	exit(1);
}

// запускаем скрипт
Utils_Emoji_Parse_To_Sample_File::doWork($lang);
