<?php

namespace Compass\Userbot;

/**
 * класс для поддержки testing-кода
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
	 * получить лимит массовых ссылок-инвайтов для тестов
	 *
	 */
	public static function getForceMassInviteLinkLimit():int {

		$value = getHeader("HTTP_FORCE_MASS_INVITE_LINK_LIMIT");
		return (int) $value;
	}
}