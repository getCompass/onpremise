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

	/**
	 * формируем заголовок и содержимое письма
	 *
	 * @return array
	 * @throws LocaleTextNotFound
	 */
	public static function make(array $config, string $group, string $locale, array $replacement):array {

		$title   = \BaseFrame\System\Locale::getText($config, $group, "title", $locale, $replacement);
		$content = \BaseFrame\System\Locale::getText($config, $group, "content", $locale, $replacement);

		return [$title, $content];
	}
}