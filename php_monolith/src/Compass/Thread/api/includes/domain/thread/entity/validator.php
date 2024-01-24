<?php

namespace Compass\Thread;

use BaseFrame\Exception\Request\ParamException;

/**
 * класс для валидации данных вводимых пользователем
 */
class Domain_Thread_Entity_Validator {

	public const MAX_THREAD_MENU_COUNT = 200;  // максимальный count в getMenu

	/**
	 * проверка offset
	 *
	 * @throws \paramException
	 */
	public static function assertOffset(int $offset):void {

		if ($offset < 0) {
			throw new ParamException("incorrect offset");
		}
	}

	/**
	 * проверка filter
	 *
	 * @throws \paramException
	 */
	public static function assertFilter(int $filter):void {

		if ($filter > 1 || $filter < -1) {
			throw new ParamException("incorrect filter {$filter}");
		}
	}

	/**
	 * получаем max thread count
	 *
	 * @throws \paramException
	 */
	public static function getMaxThreadCount(int $count):int {

		if ($count < 0) {
			throw new ParamException("incorrect count");
		}

		return limit($count, 1, self::MAX_THREAD_MENU_COUNT);
	}
}
