<?php

namespace Compass\Company;

/**
 * Класс для поддержки version api
 * Работает через заголовки, чтобы удобно рулить все в одном месте
 */
class Type_System_Version {

	/**
	 * поддерживается ли новая версия карточки с фильтрацией дополнительных полей
	 */
	public static function isEmployeeCardExtraPermissionFilter():bool {

		$value = getHeader("HTTP_X_EMPLOYEE_CARD_EXTRA_PERMISSION_FILTER");
		$value = intval($value);

		if ($value == 1) {
			return true;
		}
		return false;
	}
}