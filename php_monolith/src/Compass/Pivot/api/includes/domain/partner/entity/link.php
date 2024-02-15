<?php

namespace Compass\Pivot;

/**
 * класс для работы с партнерской ссылкой
 * @package Compass\Pivot
 */
class Domain_Partner_Entity_Link {

	/** @var string регулярка партнерской ссылки */
	protected const _PARTNER_LINK_REGEXP = "/\/welcome\/([\d]+)/";

	/**
	 * проверяем, что это партнерская ссылка
	 *
	 * @return bool
	 */
	public static function isPartnerLink(string $link):bool {

		return preg_match(self::_PARTNER_LINK_REGEXP, $link) > 0;
	}
}