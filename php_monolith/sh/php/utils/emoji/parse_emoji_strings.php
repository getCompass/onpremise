<?php declare(strict_types = 1);

use JetBrains\PhpStorm\Pure;

require_once __DIR__ . "/../../../../start.php";

/**
 * скрипт спаситель гузна есла если кто то не закинул последние последовательности эмодзи в репу
 * парсит файл emoji.json и вытаскивает обратно по юникоду все в файл emoji_strings.json
 * лежит тут, а не в репе emoji, потому что есть всякие полезности вроде debug/console
 * сам emoji.json можно достать с паблика с билда электрона
 */
class Utils_Emoji_Generate_Emoji_Parse_Emoji_Strings {

	// пусть до файла с которого берем ключевые слова
	protected const _EMOJI_KEYWORDS_LIST_FILE_PATH = "emoji.json";

	// пусть до файла куда сохраяем итоговый конфиг
	protected const _EMOJI_KEYWORDS_CONFIG_FILE_PATH = "emoji_strings.json";

	/**
	 * достаем последовательности
	 * @long
	 */
	public static function doWork():void {

		// ругаемся, если это что то выше локалки
		assertTestServer();

		// получаем массив эмодзи с ключевыми словами
		$emoji_json_list = self::_getEmojiKeywordsList();

		// раскидываем по категориям
		$short_name_list = [
			"Smileys & People" => [],
			"Travel & Places"  => [],
			"Objects"          => [],
			"Animals & Nature" => [],
			"Activities"       => [],
			"Symbols"          => [],
			"Food & Drink"     => [],
			"Flags"            => [],
			"Skin Tones"       => [],
		];

		// массив с эмодзи по ордеру юникодом
		$unicode_list = $short_name_list;

		// итоговый массив строк
		$result_list = $short_name_list;

		// бежим по всем эмодзи из emoji.json, доставая нужны поля
		foreach ($emoji_json_list as $emoji) {

			$set = [
				"short_name" => $emoji["short_name"],
				"sort_order" => $emoji["sort_order"],
				"unified"    => $emoji["unified"],
			];

			// если есть скинтоны, достаем
			if (isset($emoji["skin_variations"])) {

				$skin_list = [];
				foreach ($emoji["skin_variations"] as $k => $skin) {
					$skin_list[] = [
						"skin_tone_code" => $k,
						"unified"        => $skin["unified"],
					];
				}

				$set["skin_variations"] = $skin_list;
			}

			$short_name_list[$emoji["category"]][] = $set;
		}

		// сортируем внутри категории по сортордер
		foreach ($short_name_list as $k => $category) {

			// сортируем по sort_order в порядке возрастания
			usort($category, function(array $a, array $b) {

				return $a["sort_order"] <=> $b["sort_order"];
			});

			$short_name_list[$k] = $category;
		}

		// бежим по каждой категории
		foreach ($short_name_list as $k1 => $category) {

			// бежим по эмодзи из категории
			foreach ($category as $k2 => $emoji) {

				// если это одноцветное эмодзи
				if (!isset($emoji["skin_variations"])) {
					$unicode_list[$k1][] = (string) self::_getEmoji($emoji["unified"]);
					continue;
				}

				// если есть скинтоны
				$unicode_list[$k1][] = (string) self::_getEmoji($emoji["unified"]);
				foreach ($emoji["skin_variations"] as $skin) {
					$unicode_list[$k1][] = (string) self::_getEmoji($skin["unified"]);
				}
			}
		}

		// сливаем эмодзи в строки по категориям
		foreach ($unicode_list as $k1 => $category) {

			foreach ($category as $emoji) {

				$result_list[$k1] .= $emoji;
			}
		}

		// пишем конфиг в файл
		self::_writeConfigToFile($result_list);
	}

	/**
	 * получаем обратно эмодзи
	 */
	#[Pure] protected static function _getEmoji(string $unified):string {

		$unicode_array = explode("-", $unified);

		// магия позволяющая скормить юникод php как эмодзи, чтобы на выходе в json нормально получить картинку
		$emoji = "";
		foreach ($unicode_array as $code) {

			$uni   = "{" . $code;
			$str   = "\u$uni}";
			$emoji .= $str;
		}

		eval("\$emoji = \"$emoji\";");
		return $emoji;
	}

	/**
	 * получаем массив ключевых слов для поиска с файла
	 */
	protected static function _getEmojiKeywordsList():array {

		$file_path    = self::_EMOJI_KEYWORDS_LIST_FILE_PATH;
		$file_content = file_get_contents($file_path);

		if ($file_content === false) {
			return [];
		}

		return fromJson($file_content);
	}

	/**
	 * сохраняем в файл
	 */
	protected static function _writeConfigToFile(array $emoji_keywords_list):void {

		@file_put_contents(self::_EMOJI_KEYWORDS_CONFIG_FILE_PATH, json_encode($emoji_keywords_list, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
	}
}

// запускаем скрипт
Utils_Emoji_Generate_Emoji_Parse_Emoji_Strings::doWork();