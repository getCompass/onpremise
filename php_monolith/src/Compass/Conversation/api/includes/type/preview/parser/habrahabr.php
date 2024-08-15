<?php

namespace Compass\Conversation;

use BaseFrame\Server\ServerProvider;

/**
 * класс для парсинга ссылок на habrahabr.ru
 * @package Compass\Conversation
 */
class Type_Preview_Parser_Habrahabr extends Type_Preview_Parser_Helper implements Type_Preview_Parser_Interface {

	public const DOMAIN = "habr.com";

	protected const _SITE_NAME = "Habrahabr";

	/**
	 * функция для определения – уместна ли переданная ссылка для парсинга конкретным парсером
	 *
	 * @return bool
	 */
	public static function isRelevantUrl(string $url):bool {

		return inHtml($url, Type_Preview_Parser_Habrahabr::DOMAIN) || ServerProvider::isTest() && inHtml($url, mb_strtolower(self::_SITE_NAME));
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
		if (self::_isProfile($type) || self::_isProfileForHabr($html)) {
			return Type_Preview_Parser_Default::makeDataForSiteByType(PREVIEW_TYPE_PROFILE, $user_id, $url, $short_url, self::DOMAIN, self::_SITE_NAME, $html);
		}

		// если ссылка на пост
		if (self::_isContent($type)) {
			return self::_makeDataForHabrByContent($user_id, $url, $short_url, self::DOMAIN, self::_SITE_NAME, $html);
		}

		// иначе парсим по дефолту
		return Type_Preview_Parser_Default::makeDataForSiteByHtml($type, $user_id, $url, $short_url, self::_SITE_NAME, $html);
	}

	// если это ресурс
	protected static function _isResource(string $url):bool {

		// если это конец строки на .com то отдаем как домен
		preg_match("/.com(|\/|(?|#).*)$/i", $url, $matches);

		// парсим как ресурс
		if (count($matches) > 0) {
			return true;
		}

		// если это конец строки на .ru то отдаем как домен
		preg_match("/.ru(|\/|(?|#).*)$/i", $url, $matches);

		// парсим как ресурс
		if (count($matches) > 0) {
			return true;
		}

		return false;
	}

	// проверяем что ссылка профиль на habr.com
	protected static function _isProfileForHabr(string $html):bool {

		// проходимся регуляркой по html и ищем соотвествующий тег
		if (!preg_match("/<div class=\"user_profile\">/ui", $html, $matches)) {
			return false;
		}

		return true;
	}

	// проверяем, что ссылка на пост
	protected static function _isContent(string $type):bool {

		return $type == self::_CONTENT_TYPE_VIDEO || "article";
	}

	// создаем превью для хабра с контентом
	protected static function _makeDataForHabrByContent(int $user_id, string $url, string $short_url, string $domain, string $site_name, string $html):array {

		$title            = self::_getTitle($html);
		$description      = self::_getDescription($html);
		$favicon_file_map = self::_tryDownloadFavicon($user_id, $short_url, $html);

		// качаем изображение, если есть
		$prepared_html  = self::_getPreparedHtml($html);
		$image_file_map = self::_tryDownloadImage($user_id, $short_url, $prepared_html);

		return Type_Preview_Formatter::prepareDataForStorageByType(
			PREVIEW_TYPE_CONTENT,
			$url,
			$domain,
			$site_name,
			$title,
			$favicon_file_map,
			$image_file_map,
			$description
		);
	}

	// получаем подготовленный в html
	public static function _getPreparedHtml(string $html):string {

		// получаем html поста, отрезая что выше
		$prepared_html = mb_stristr($html, "<div class=\"post__body post__body_full\">");

		// получаем html поста, если мобильная версии сайта, отрезая что выше
		if (mb_strlen($prepared_html) < 1) {
			$prepared_html = mb_stristr($html, "<div class=\"tm-page-article__hubs\">");
		}

		// если не удалось получить, возращаем первоначальный html
		if (mb_strlen($prepared_html) < 1) {
			$prepared_html = $html;
		}

		return $prepared_html;
	}
}