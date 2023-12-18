<?php

namespace Compass\Company;

/**
 * Класс для очистки данных компании
 */
class Domain_Company_Entity_Sanitizer {

	// регулярка для названия компании
	protected const _COMPANY_NAME_REGEXP = "/[^а-яёa-z0-9[:punct:]№ ẞßÄäÜüÖöÀàÈèÉéÌìÍíÎîÒòÓóÙùÚúÂâÊêÔôÛûËëÏïŸÿÇçÑñ¿¡]|[<>]/ui";

	/**
	 * Очистка названии компании от лишних символов
	 */
	public static function sanitizeCompanyName(string $full_name):string {

		// удаляем весь левак
		return trim(preg_replace([self::_COMPANY_NAME_REGEXP, "/[ ]{2,}/u"], ["", " "], $full_name));
	}
}