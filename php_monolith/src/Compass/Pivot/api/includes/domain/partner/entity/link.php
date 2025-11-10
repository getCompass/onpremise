<?php

namespace Compass\Pivot;

/**
 * класс для работы с партнерской ссылкой
 * @package Compass\Pivot
 */
class Domain_Partner_Entity_Link {

	/** @var string регулярка партнерской ссылки */
	protected const _PARTNER_LINK_REGEXP = "/\/welcome\/([\d]+)/";

	/** @var string регулярка менеджерской ссылки */
	protected const _MANAGER_LINK_REGEXP = "/\/partner/";

	/**
	 * проверяем, что это партнерская ссылка
	 *
	 * @return bool
	 */
	public static function isPartnerLink(string $link):bool {

		return preg_match(self::_PARTNER_LINK_REGEXP, $link) > 0;
	}

	/**
	 * проверяем, что это менеджерская ссылка
	 *
	 * @return bool
	 */
	public static function isManagerLink(string $link):bool {

		return preg_match(self::_MANAGER_LINK_REGEXP, $link) > 0;
	}
}