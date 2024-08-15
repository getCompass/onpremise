<?php

namespace Compass\Thread;

/**
 * класс для парсинга любых ссылок (используется по умолчанию)
 */
class Type_Preview_Parser_Default extends Type_Preview_Parser_Helper implements Type_Preview_Parser_Interface {

	/**
	 * функция для подготовки ссылки перед непосредственным парсингом (перед curl запросом)
	 *
	 * @return string
	 */
	public static function prepareUrl(string $url):string {

		return $url;
	}

	// создаем превью
	public static function makeDataFromHtml(string $user_id, string $url, string $short_url, string $html):array {

		$type         = self::_getType($html);
		$preview_type = self::_convertContentType($type);

		$title            = self::_getTitle($html);
		$description      = self::_getDescription($html);
		$site_name        = self::_getSiteName($html, $short_url);
		$favicon_file_map = self::_tryDownloadFavicon($user_id, $short_url, $html, Type_Preview_Utils::getProtocolByUrl($url));

		// качаем изображение, если есть
		$image_file_map = self::_tryDownloadImage($user_id, $short_url, $html, Type_Preview_Utils::getProtocolByUrl($url));

		return Type_Preview_Formatter::prepareDataForStorageByType(
			$preview_type,
			$url,
			$short_url,
			$site_name,
			$title,
			$favicon_file_map,
			$image_file_map,
			$description
		);
	}

	/**
	 * функция для создания превью из mime type application/json
	 *
	 * @return array
	 */
	public static function makeDataFromJson(string $user_id, string $url, string $short_url, array $content):array {

		throw new cs_UrlParseFailed("unsupported mime type", Type_Logs_Cron_Parser::LOG_STATUS_PARSE_ERROR);
	}

	// создаем превью для конкретнго сайта
	public static function makeDataForSiteByHtml(string $content_type, int $user_id, string $url, string $domain, string $site_name, string $html):array {

		$preview_type     = self::_convertContentType($content_type);
		$title            = self::_getTitle($html);
		$description      = self::_getDescription($html);
		$favicon_file_map = self::_tryDownloadFavicon($user_id, $domain, $html, Type_Preview_Utils::getProtocolByUrl($url));

		// качаем изображение, если есть
		$image_file_map = self::_tryDownloadImage($user_id, $domain, $html, Type_Preview_Utils::getProtocolByUrl($url));

		return Type_Preview_Formatter::prepareDataForStorageByType(
			$preview_type,
			$url,
			$domain,
			$site_name,
			$title,
			$favicon_file_map,
			$image_file_map,
			$description
		);
	}

	// создаем превью для конкретнго сайта
	public static function makeDataForSiteByType(string $preview_type, int $user_id, string $url, string $short_url, string $domain, string $site_name, string $html):array {

		$title            = self::_getTitle($html);
		$description      = self::_getDescription($html);
		$favicon_file_map = self::_tryDownloadFavicon($user_id, $short_url, $html, Type_Preview_Utils::getProtocolByUrl($url));

		// качаем изображение, если есть
		$image_file_map = self::_tryDownloadImage($user_id, $short_url, $html, Type_Preview_Utils::getProtocolByUrl($url));

		return Type_Preview_Formatter::prepareDataForStorageByType(
			$preview_type,
			$url,
			$domain,
			$site_name,
			$title,
			$favicon_file_map,
			$image_file_map,
			$description
		);
	}

	/**
	 * функция для определения – уместна ли переданная ссылка для парсинга конкретным парсером
	 *
	 * @return bool
	 */
	public static function isRelevantUrl(string $url):bool {

		return true;
	}
}
