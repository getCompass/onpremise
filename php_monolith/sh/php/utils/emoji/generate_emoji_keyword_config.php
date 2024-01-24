<?php declare(strict_types = 1);

namespace Compass\Thread;

use BaseFrame\Conf\Emoji;
use BaseFrame\Exception\Domain\ReturnFatalException;

require_once __DIR__ . "/../../../../start.php";

/**
 * скрипт для генерации конфига эмодзи с ключевыми словами на русском языке
 * 1) берем конфиг готовый конфиг из тз или спрашиваем ПМ/ПО [emoji -> keyword] и кладем его в папку рядом со скриптом (Replace.json)
 * 2) дергаем скрипт
 * 3) подменяем сгенеренным конфигом то что лежит в emojikeywords в php_pivot в api/conf в нужную версию
 * 4) подменяем в www/default_files сам файл на новый сгенеренный
 */
class Utils_Emoji_Generate_Emoji_Keyword_Config {

	// пусть до файла с которого берем ключевые слова
	protected const _EMOJI_KEYWORDS_LIST_FILE_PATH = "replace.json";

	// пусть до файла куда сохраяем итоговый конфиг
	protected const _EMOJI_KEYWORDS_CONFIG_FILE_PATH = "emoji_keywords";

	/**
	 * генерируем файл
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function doWork(string $lang, int $version):void {

		// ругаемся, если это что то выше локалки
		assertTestServer();

		// получаем массив эмодзи с php_thread
		$emoji_list = self::_getEmojiList();

		// получаем массив эмодзи с ключевыми словами
		$emoji_keywords_list = self::_getEmojiKeywordsList();

		// генерируем конфиг где все в месте: emoji/keywords/shortname
		$emoji_keywords_config = self::_generateEmojiKeywordsConfig($emoji_list, $emoji_keywords_list);

		// если в конфиге остались пустые эмодзи
		$emoji_keywords_config = self::_fixEmptyShortname($emoji_list, $emoji_keywords_config);

		// проверяем на запрещенные символы
		self::_checkForbiddenChar($emoji_keywords_config);

		// пишем конфиг в файл
		self::_writeConfigToFile($emoji_keywords_config, $lang, $version);
	}

	/**
	 * получаем массив emoji из конфига php_thread
	 */
	protected static function _getEmojiList():array {

		return array_merge(Emoji::EMOJI_LIST, Emoji::EMOJI_FLAG_LIST);
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
	 * создаем новый конфиг с эмодзи шортнеймами и переводом
	 */
	protected static function _generateEmojiKeywordsConfig(array $emoji_list, array $emoji_keywords_list):array {

		// проходимся по конфигу ключевых слов
		$result = [];
		foreach ($emoji_keywords_list as $k1 => $v1) {

			$result[] = [
				"emoji"     => $v1["emoji"],
				"shortname" => "",
				"keywords"  => $v1["keywords"],
			];

			foreach ($emoji_list as $k2 => $v2) {

				if ($v1["emoji"] == $k2) {
					$result[$k1]["shortname"] = $v2;
				}
			}
		}

		return $result;
	}

	/**
	 * ищем пустые эмоджи
	 */
	protected static function _fixEmptyShortname(array $emoji_list, array $emoji_keywords_config):array {

		foreach ($emoji_keywords_config as $k1 => $v1) {

			if ($v1["shortname"] != "") {
				continue;
			}

			foreach ($emoji_list as $k2 => $v2) {

				if ((mb_ord($v1["emoji"], "UTF-8")) == (mb_ord($k2, "UTF-8"))) {

					$emoji_keywords_config[$k1] = [

						"emoji"     => $v1["emoji"],
						"shortname" => $v2,
						"keywords"  => $v1["keywords"],
					];
					break;
				}
			}
		}

		return $emoji_keywords_config;
	}

	/**
	 * проверяем на запрещенные символы
	 */
	protected static function _checkForbiddenChar(array $emoji_keywords_config):void {

		// массив запрещенных символов
		$forbidden_char_list = [
			"|",
			"/",
		];

		// проходимся по каждому символу
		foreach ($forbidden_char_list as $char) {

			$is_found = strripos(json_encode($emoji_keywords_config), $char);

			// если нашли такой
			if ($is_found == true) {

				console("в конфиге есть запрещенных символы");
				throw new ReturnFatalException("forbidden char");
			}
		}
	}

	/**
	 * сохраняем в файл
	 */
	protected static function _writeConfigToFile(array $emoji_keywords_list, string $lang, int $version):void {

		// формируем путь до генерируемого файла
		$save_path = self::_EMOJI_KEYWORDS_CONFIG_FILE_PATH;
		$save_path = "{$save_path}_{$lang}_{$version}.json";

		@file_put_contents($save_path, json_encode($emoji_keywords_list, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT));
		console("Успешно сохранили конфиг {$save_path}");
	}
}

console("Введите lang (ru или en)");
$lang = (trim(readline()));
if (mb_strlen($lang) < 1) {

	console("Передан неверный {$lang}");
	exit(1);
}

console("Введите version для конфига (1,2,3...)");
$version = intval(trim(readline()));
if ($version < 1) {

	console("Передан неверный version");
	exit(1);
}

// запускаем скрипт
Utils_Emoji_Generate_Emoji_Keyword_Config::doWork($lang, $version);