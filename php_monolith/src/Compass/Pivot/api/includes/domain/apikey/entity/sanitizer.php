<?php

namespace Compass\Pivot;

/**
 * Класс для очистки данных ключа
 */
class Domain_Apikey_Entity_Sanitizer {

	/**
	 * Очистка названия ключа олт лишних символов
	 */
	public static function sanitizeApikeyName(string $name):string {

		return trim(preg_replace([
			\BaseFrame\System\Character::EMOJI_REGEX,
			\BaseFrame\System\Character::COMMON_FORBIDDEN_CHARACTER_REGEX,
			\BaseFrame\System\Character::ANGLE_BRACKET_REGEX,
			\BaseFrame\System\Character::FANCY_TEXT_REGEX,
			\BaseFrame\System\Character::DOUBLE_SPACE_REGEX,
			\BaseFrame\System\Character::NEWLINE_REGEX,
		], ["", "", "", "", " ", ""], $name));
	}
}