<?php

namespace Compass\Conversation;

use BaseFrame\Server\ServerProvider;

/**
 * Класс для парсинга ссылок Compass
 */
class Type_Preview_Parser_Compass extends Type_Preview_Parser_Helper implements Type_Preview_Parser_Interface {

	public const    DOMAIN     = PUBLIC_ADDRESS_GLOBAL;
	protected const _SITE_NAME = "Compass";

	protected const _ONPREMISE_POSTFIX = "On-premise";

	// возможные домены верхнего уровня для compass
	protected const _TOP_DOMAIN_LIST = ["ru", "com", PUBLIC_ADDRESS_GLOBAL];

	// список доменов верхнего уровня для отдачи с типом resource
	protected const _TOP_DOMAIN_RESOURCE_LIST = ["com", "apitest.team"];

	/**
	 * функция для определения – уместна ли переданная ссылка для парсинга конкретным парсером
	 *
	 * @return bool
	 */
	public static function isRelevantUrl(string $url):bool {

		if (str_contains($url, Type_Preview_Parser_Compass::DOMAIN)
			|| preg_match("/getcompass.(ru|com)(\/|$)/iu", $url)
			|| preg_match(sprintf("/%s(\/|$)/iu", str_replace("/", "\/", PUBLIC_ADDRESS_GLOBAL)), $url)) {

			return true;
		}

		foreach (PUBLIC_ENTRYPOINT_JOIN_VARIETY as $variety) {

			$domain_string = str_starts_with(WEB_PROTOCOL_PUBLIC, $variety)
				? mb_substr($variety, mb_strlen(WEB_PROTOCOL_PUBLIC))
				: $variety;

			if (str_contains($url, $domain_string)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * функция для подготовки ссылки перед непосредственным парсингом (перед curl запросом)
	 *
	 * @return string
	 */
	public static function prepareUrl(string $url):string {

		return $url;
	}

	/**
	 * Создаем превью
	 */
	public static function makeDataFromHtml(string $user_id, string $url, string $short_url, string $html):array {

		// если это инвайт компаса
		if (self::_isInviteInCompany($url) || self::_isInviteInCompass($url) || self::_isPartnerInviteInCompass($url)) {

			return Type_Preview_Parser_Default::makeDataForSiteByType(
				PREVIEW_TYPE_COMPASS_INVITE, $user_id, $url, $short_url, self::DOMAIN, self::_getSiteNameForData(), $html
			);
		}

		// если это ресурс или ссылка на пользовательское соглашение компаса
		if (self::_isResource($url) || self::_isCompassSpecialPages($url)) {

			return Type_Preview_Parser_Default::makeDataForSiteByType(
				PREVIEW_TYPE_RESOURCE, $user_id, $url, $short_url, self::DOMAIN, self::_getSiteNameForData(), $html
			);
		}

		// получим тип с сайта
		$type = self::_getType($html);

		// иначе парсим по дефолту
		return Type_Preview_Parser_Default::makeDataForSiteByHtml($type, $user_id, $url, $short_url, self::_getSiteNameForData(), $html);
	}

	/**
	 * функция для создания превью из mime type application/json
	 *
	 * @return array
	 */
	public static function makeDataFromJson(string $user_id, string $url, string $short_url, array $content):array {

		throw new cs_UrlParseFailed("unsupported mime type", Type_Logs_Cron_Parser::LOG_STATUS_PARSE_ERROR);
	}

	/**
	 * Проверяем, что ссылка приглашение в компанию
	 */
	protected static function _isInviteInCompany(string $url):bool {

		// сначала сверяем на соответствие любой из join точек входа
		foreach (PUBLIC_ENTRYPOINT_JOIN_VARIETY as $variety) {

			if ($variety === "") {
				continue;
			}

			$domain_string = str_starts_with(WEB_PROTOCOL_PUBLIC, $variety)
				? mb_substr($variety, mb_strlen(WEB_PROTOCOL_PUBLIC))
				: $variety;

			if (preg_match("#($domain_string)/([a-zA-Z0-9]{8}/?)$#i", $url)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Проверяем, что ссылка приглашение в компас
	 */
	protected static function _isInviteInCompass(string $url):bool {

		$top_domain_string = self::_getTopDomainString(self::_TOP_DOMAIN_LIST);
		return (bool) preg_match("/" . "($top_domain_string)\/invite\//i", $url);
	}

	/**
	 * Проверяем, что ссылка приглашение партнера в компас
	 */
	protected static function _isPartnerInviteInCompass(string $url):bool {

		$top_domain_string = self::_getTopDomainString(self::_TOP_DOMAIN_LIST);
		return (bool) preg_match("/" . "($top_domain_string)\/pp\//i", $url);
	}

	/**
	 * Проверяем являяется ли URL ресурсом
	 */
	protected static function _isResource(string $url):bool {

		$top_domain_string = self::_getTopDomainString(self::_TOP_DOMAIN_RESOURCE_LIST);

		return (bool) preg_match("/.($top_domain_string)(|\/|)$/i", $url);
	}

	/**
	 * Проверяем что ссылка на спец. страницу компаса которая должна отдаваться как ресурс
	 */
	protected static function _isCompassSpecialPages(string $url):bool {

		return (bool) preg_match("/" . self::DOMAIN . "\/(welcome|agreement)\/?$/i", $url);
	}

	/**
	 * Преобразуем домен верхнего уровня в строку
	 */
	protected static function _getTopDomainString(array $top_domain_list):string {

		return addcslashes(implode("|", $top_domain_list), ".");
	}

	/**
	 * Получить имя сайта для превью.
	 */
	protected static function _getSiteNameForData():string {

		if (ServerProvider::isOnPremise()) {
			return self::_SITE_NAME . " " . self::_ONPREMISE_POSTFIX;
		}

		return self::_SITE_NAME;
	}
}
