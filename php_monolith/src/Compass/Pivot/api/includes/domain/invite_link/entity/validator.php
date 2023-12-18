<?php

namespace Compass\Pivot;

/**
 * Проверяем что это
 */
class Domain_InviteLink_Entity_Validator {

	protected const _MIN_LENGTH = 1;
	protected const _MAX_LENGTH = 16;

	// регулярное выражение с запрещенными символами
	protected const _NOT_ALLOW_CHAR_REGEX = "/[^_.\p{L}\d-]+/u";

	// символы для приглашения для промокода
	protected const _PREG_PERMANENT_REGEXP = "([\s\S][^\/?]{1,})";

	/**
	 * Проверяем что это invite ссылка
	 */
	public static function isInviteLink(string $link):bool {

		return self::isPermanentInviteLink($link);
	}

	/**
	 * Проверяем что это промокод
	 */
	public static function isPermanentInviteLink(string $link):bool {

		$matches = [];
		preg_match("/\/(invite|pp)\/" . self::_PREG_PERMANENT_REGEXP . "/", $link, $matches);

		if (!isset($matches[2]) || substr_count($link, "http") > 1) {
			return false;
		}

		$invite_code = $matches[2];

		if (!self::isValidPermanentInviteCode($invite_code)) {
			return false;
		}

		return true;
	}

	/**
	 * Получаем промокод из ссылки
	 */
	public static function getPermanentInviteCode(string $link):string {

		$matches = [];
		preg_match("/\/(invite|pp)\/" . self::_PREG_PERMANENT_REGEXP . "/", $link, $matches);

		return $matches[2];
	}

	/**
	 * Получаем инвайт код из ссылки
	 */
	public static function getInviteCodeFromLink(string $link):string {

		if (self::isPermanentInviteLink($link)) {

			return self::getPermanentInviteCode($link);
		}

		throw new Domain_Link_Exception_LinkNotFound();
	}

	/**
	 *Проверяем что передали валидный промокод
	 */
	public static function isValidPermanentInviteCode(string $invite_code):bool {

		// если не соответствует длина
		$length = mb_strlen($invite_code);
		if ($length < self::_MIN_LENGTH || $length > self::_MAX_LENGTH) {
			return false;
		}

		// если промокод содержит какие-то запрещенные символы кроме букв (любого алфавита) и цифр
		if (preg_match(self::_NOT_ALLOW_CHAR_REGEX, $invite_code)) {
			return false;
		}

		return true;
	}
}