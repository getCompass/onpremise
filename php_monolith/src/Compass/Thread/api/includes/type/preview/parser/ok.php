<?php

namespace Compass\Thread;

// класс для парсинга ссылок на ok.ru
class Type_Preview_Parser_Ok extends Type_Preview_Parser_Helper {

	public const DOMAIN = "ok.ru";

	protected const _SITE_NAME = "Ok";

	// создаем превью
	public static function makeData(string $user_id, string $url, string $short_url, string $html):array {

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