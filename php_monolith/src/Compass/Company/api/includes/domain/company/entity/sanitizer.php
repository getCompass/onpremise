<?php

namespace Compass\Company;

/**
 * Класс для очистки данных компании
 */
class Domain_Company_Entity_Sanitizer {

	/**
	 * Очистка названии компании от лишних символов
	 */
	public static function sanitizeCompanyName(string $full_name):string {

		// удаляем весь левак
		return trim(preg_replace([
			\BaseFrame\System\Character::EMOJI_REGEX,
			\BaseFrame\System\Character::COMMON_FORBIDDEN_CHARACTER_REGEX,
			\BaseFrame\System\Character::ANGLE_BRACKET_REGEX,
			\BaseFrame\System\Character::FANCY_TEXT_REGEX,
			\BaseFrame\System\Character::DOUBLE_SPACE_REGEX,
			\BaseFrame\System\Character::NEWLINE_REGEX,
		], ["", "", "", "", " ", ""], $full_name));
	}
}