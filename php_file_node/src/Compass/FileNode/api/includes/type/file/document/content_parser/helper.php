<?php

namespace Compass\FileNode;

/**
 * Класс содержит вспомогательные функции для парсинга содержимого документов
 */
class Type_File_Document_ContentParser_Helper {

	/**
	 * Проверяем строку на содержание бинарных данных
	 *
	 * @return bool
	 */
	public static function isContainBinary(string $value):bool {

		return false === mb_detect_encoding($value, null, true);
	}

	/**
	 * Очищаем строку от бинарных данных
	 *
	 * @return string
	 */
	public static function clearBinary(string $input):string {

		return filter_var($input, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
	}

	/**
	 * Подготавливаем текст
	 *
	 * @return string
	 */
	public static function prepareText(string $text):string {

		// проверяем на наличие бинарных данных
		if (self::isContainBinary($text)) {
			$text = self::clearBinary($text);
		}

		return $text;
	}
}