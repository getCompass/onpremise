<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\LocaleTextNotFound;

/**
 * класс для работы с содержимом писем
 * @package Compass\Pivot
 */
class Type_Mail_Content {

	/** список всех существующих шаблонов */
	public const TEMPLATE_MAIL_REGISTRATION  = "mail_registration";
	public const TEMPLATE_MAIL_AUTHORIZATION = "mail_authorization";
	public const TEMPLATE_MAIL_RESTORE       = "mail_restore";
	public const TEMPLATE_MAIL_ADD           = "mail_add";
	public const TEMPLATE_MAIL_CHANGE        = "mail_change";

	/** дефолтные значения */
	public const DEFAULT_TITLE   = "Confirm code – {confirm_code}";
	public const DEFAULT_CONTENT = "Your code:<h3>{confirm_code}</h3>";

	/**
	 * формируем заголовок и содержимое письма
	 *
	 * @return array
	 */
	public static function make(array $config, string $group, string $locale, array $replacement):array {

		// получаем заголовок письма
		try {
			$title = \BaseFrame\System\Locale::getText($config, $group, "title", $locale, $replacement);
		} catch (LocaleTextNotFound) {
			$title = self::DEFAULT_TITLE;
		}

		// получаем содержимое письма
		try {
			$content = \BaseFrame\System\Locale::getText($config, $group, "content", $locale, $replacement);
		} catch (LocaleTextNotFound) {
			$content = self::DEFAULT_CONTENT;
		}

		return [$title, $content];
	}
}