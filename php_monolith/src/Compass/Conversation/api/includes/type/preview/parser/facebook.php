<?php

namespace Compass\Conversation;

use BaseFrame\Server\ServerProvider;

// класс для парсинга ссылок на facebook
class Type_Preview_Parser_Facebook extends Type_Preview_Parser_Default {

	public const DOMAIN = "facebook.com";

	protected const _SITE_NAME                 = "Facebook";
	protected const _TYPE_PROFILE_PATTERN_LIST = [
		0 => [
			"pattern"   => "/<meta[^>]+property=['\"]al:ios:url['\"][^>]+content=['\"]([^\"]*?)['\"].*?>/i",
			"match_num" => 1,
		],
	];

	// создаем превью
	public static function makeData(string $user_id, string $url, string $short_url, string $html):array {

		$type = self::_getType($html);

		// если это профиль
		if (self::_isFacebookProfile($type, $html)) {
			return Type_Preview_Parser_Default::makeDataForSiteByType(PREVIEW_TYPE_PROFILE, $user_id, $url, $short_url, self::DOMAIN, self::_SITE_NAME, $html);
		}

		// если это конец строки то отдаем как домен
		if (self::_isResource($url)) {
			return Type_Preview_Parser_Default::makeDataForSiteByType(PREVIEW_TYPE_RESOURCE, $user_id, $url, $short_url, self::DOMAIN, self::_SITE_NAME, $html);
		}

		return Type_Preview_Parser_Default::makeDataForSiteByHtml($type, $user_id, $url, $short_url, self::_SITE_NAME, $html);
	}

	// если это профиль
	protected static function _isFacebookProfile(string $type, string $html):bool {

		if (parent::_isProfile($type)) {
			return true;
		}

		$profile_property = self::_tryGetMatchFromPatternList($html, self::_TYPE_PROFILE_PATTERN_LIST);
		if (inHtml($profile_property, "profile")) {
			return true;
		}

		return false;
	}

	// если это ресурс
	protected static function _isResource(string $url):bool {

		// если это конец строки то отдаем как домен
		preg_match("/.com(|\/|(?|#).*)$/i", $url, $matches);

		// парсим как ресурс
		if (count($matches) > 0 || ServerProvider::isTest()) {
			return true;
		}

		return false;
	}
}
