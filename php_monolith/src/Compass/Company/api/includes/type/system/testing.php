<?php

namespace Compass\Company;

/**
 * Класс для поддержки testing-кода
 * работает через заголовки, чтобы удобно рулить все в одном месте
 */
class Type_System_Testing {

	/**
	 * функция срабатывает перед тем как вызвать любой из методов
	 *
	 * @throws \parseException
	 */
	public static function __callStatic(string $name, array $arguments):void {

		assertTestServer();
	}

	/**
	 * получить лимит обычных ссылок-инвайтов для тестов
	 */
	public static function getForceRegularInviteLinkLimit():int {

		$value = getHeader("HTTP_FORCE_REGULAR_INVITE_LINK_LIMIT");
		return (int) $value;
	}

	/**
	 * получаем флаг нужно ли скипать лимит на ботов
	 */
	public static function isSkipUserbotLimit():bool {

		$value = getHeader("HTTP_SKIP_USERBOT_LIMIT");
		return (bool) $value;
	}

	/**
	 * получаем лимит на ботов для тестов
	 */
	public static function getUserbotLimit():int {

		$value = getHeader("HTTP_TEST_USERBOT_LIMIT");
		return (int) $value;
	}
}