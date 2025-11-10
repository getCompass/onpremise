<?php

namespace Compass\Company;

/**
 * Класс для очистки данных
 */
class Domain_User_Entity_Sanitizer {

	protected const _MAX_FULL_NAME_LENGTH = 40; // максимальная длина имени пользователя

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

		// удаляем лишнее
		$full_name = \BaseFrame\System\Character::sanitizeFullForbiddenCharacterRegex($full_name);

		// обрезаем
		return mb_substr($full_name, 0, self::_MAX_FULL_NAME_LENGTH);
	}
}