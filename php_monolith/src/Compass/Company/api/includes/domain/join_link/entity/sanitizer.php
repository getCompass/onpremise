<?php

namespace Compass\Company;

use JetBrains\PhpStorm\Pure;

/**
 * Класс для очистки данных ссылок-приглашений
 */
class Domain_JoinLink_Entity_Sanitizer {

	public const    MAX_LENGTH_COMMENT_TEXT = 200; // максимальная длина коммента
	protected const _MAX_PER_QUERY          = 50;  // максимальное количество результатов

	/**
	 * форматируем оффсет
	 */
	public static function sanitizeOffset(int $offset):int {

		if ($offset < 0) {
			return 0;
		}
		return $offset;
	}

	/**
	 * форматируем count
	 */
	#[Pure]
	public static function sanitizeCount(int $count):int {

		if ($count < 1) {
			$count = self::_MAX_PER_QUERY;
		}
		return limit($count, 1, self::_MAX_PER_QUERY);
	}
}