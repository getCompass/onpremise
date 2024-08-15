<?php

namespace Compass\Thread;

use BaseFrame\Server\ServerProvider;

/**
 * класс для парсинга ссылок на ok.ru
 */
class Type_Preview_Parser_Ok extends Type_Preview_Parser_Helper implements Type_Preview_Parser_Interface {

	public const DOMAIN = "ok.ru";

	protected const _SITE_NAME = "Ok";

	/**
	 * функция для определения – уместна ли переданная ссылка для парсинга конкретным парсером
	 *
	 * @return bool
	 */
	public static function isRelevantUrl(string $url):bool {

		return inHtml($url, Type_Preview_Parser_Ok::DOMAIN) || ServerProvider::isTest() && inHtml($url, mb_strtolower(self::_SITE_NAME));
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
	 * функция для создания превью из mime type application/json
	 *
	 * @return array
	 */
	public static function makeDataFromJson(string $user_id, string $url, string $short_url, array $content):array {

		throw new cs_UrlParseFailed("unsupported mime type", Type_Logs_Cron_Parser::LOG_STATUS_PARSE_ERROR);
	}

	// создаем превью
	public static function makeDataFromHtml(string $user_id, string $url, string $short_url, string $html):array {

		$type = self::_getType($html);

		// если это конец строки то отдаем как домен
		if (self::_isResource($url)) {
			return Type_Preview_Parser_Default::makeDataForSiteByType(PREVIEW_TYPE_RESOURCE, $user_id, $url, $short_url, self::DOMAIN, self::_SITE_NAME, $html);
		}

		// если ссылка на профиль
		if (self::_isProfile($type)) {
			return Type_Preview_Parser_Default::makeDataForSiteByType(PREVIEW_TYPE_PROFILE, $user_id, $url, $short_url, self::DOMAIN, self::_SITE_NAME, $html);
		}

		// если ссылка на пост
		if (self::_isContent($type)) {
			return Type_Preview_Parser_Default::makeDataForSiteByType(PREVIEW_TYPE_CONTENT, $user_id, $url, $short_url, self::DOMAIN, self::_SITE_NAME, $html);
		}

		// иначе парсим по дефолту
		return Type_Preview_Parser_Default::makeDataForSiteByHtml($type, $user_id, $url, $short_url, self::_SITE_NAME, $html);
	}

	// если это ресурс
	protected static function _isResource(string $url):bool {

		// если это конец строки то отдаем как домен
		preg_match("/.ru(|\/|(\?|\#).*)$/i", $url, $matches);

		// парсим как ресурс
		if (count($matches) > 0) {
			return true;
		}

		return false;
	}

	// проверяем, что ссылка на пост
	protected static function _isContent(string $type):bool {

		return $type == self::_CONTENT_TYPE_WEBSITE || $type == self::_CONTENT_TYPE_VIDEO;
	}
}