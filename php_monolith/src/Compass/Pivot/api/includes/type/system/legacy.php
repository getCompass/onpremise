<?php

namespace Compass\Pivot;

/**
 * Класс для поддержки legacy
 */
class Type_System_Legacy {

	/**
	 * Поддерживается ли валидация ссылки компании поддержки
	 */
	public static function isSupportCompanyErrorAllowed():bool {

		$value = getHeader("HTTP_X_COMPASS_ALLOW_SUPPORT_COMPANY_VALIDATE_ERROR");
		$value = intval($value);

		if ($value == 1) {
			return true;
		}

		return false;
	}
}