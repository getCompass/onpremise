<?php

namespace Compass\Pivot;

/**
 * класс для валидации данных вводимых пользователем о компании
 */
class Domain_Company_Entity_Filter {

	public const MAX_GET_USER_COMPANY_LIST = 50;

	/**
	 * Выбрасываем исключение если передан некорректный count
	 */
	public static function filterUserCompanyListLimit(int $limit):int {

		return limit($limit, 1, self::MAX_GET_USER_COMPANY_LIST);
	}
}
