<?php

namespace Compass\Company;

/**
 * Класс для поддержки legacy-кода
 */
class Type_System_Legacy {

	/**
	 * отдаем ли новую ошибку когда все пользователи из приглашенных были удалены
	 */
	public static function is504ErrorThenAllUserWasKicked():bool {

		$value = getHeader("HTTP_IS_ALL_USER_WAS_KICKED_ERROR");
		$value = intval($value);

		if ($value == 1) {
			return true;
		}

		return false;
	}
}