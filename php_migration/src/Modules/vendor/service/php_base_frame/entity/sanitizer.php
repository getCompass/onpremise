<?php

/**
 * Класс для очистки данных
 */
class Entity_Sanitizer {

	protected const _MAX_FULL_NAME_LENGTH = 40;    // максимальная длина имени пользователя
	protected const _PROFILE_NAME_REGEXP  = "/[^а-яёa-z0-9\-' œẞßÄäÜüÖöÀàÈèÉéÌìÍíÎîÒòÓóÙùÚúÂâÊêÔôÛûËëÏïŸÿÇçÑñ]|[<>]/ui";

	/**
	 * Очистка имени от лишних символов
	 *
	 * @param string $full_name
	 *
	 * @return string
	 */
	public static function sanitizeProfileName(string $full_name):string {

		// если текст состоит только из символов
		if (preg_match("/^[^[:alnum:]]+$/u", $full_name)) {

			return "";
		}

		// удаляем
		$full_name = trim(preg_replace([self::_PROFILE_NAME_REGEXP, "/[ ]{2,}/u"], ["", " "], $full_name));

		// обрезаем
		return mb_substr($full_name, 0, self::_MAX_FULL_NAME_LENGTH);
	}

	/**
	 * Удалить все utf8mb4 символы из строки перед отправкой запроса в БД
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public static function sanitizeUtf8Query(string $text):string {

		return preg_replace("/[\xF0-\xF7].../s", "", $text);
	}
}