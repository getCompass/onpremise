<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Server\ServerProvider;

/**
 * класс для парсинга ссылок на youtube.com
 */
class Type_Preview_Parser_Youtube extends Type_Preview_Parser_Helper {

	public const DOMAIN = "youtube.com";

	protected const _CONTENT_TYPE_PROFILE = "yt-fb-app:channel";
	protected const _VIDEO_TYPE           = "video.other";
	protected const _SITE_NAME            = "YouTube";

	// создаем превью
	public static function makeData(string $user_id, string $url, string $short_url, string $html):array {

		$type      = self::_getType($html);
		$video_url = self::_getVideoUrl($html, $short_url);

		if (self::_isProfile($type)) {
			return Type_Preview_Parser_Default::makeDataForSiteByType(PREVIEW_TYPE_PROFILE, $user_id, $url, $short_url, self::DOMAIN, self::_SITE_NAME, $html);
		}

		if (self::_isContent($type)) {
			return Type_Preview_Parser_Default::makeDataForSiteByType(PREVIEW_TYPE_CONTENT, $user_id, $url, $short_url, self::DOMAIN, self::_SITE_NAME, $html);
		}

		if (self::_isVideo($type, $video_url)) {
			return self::_makeVideoData($user_id, $url, $short_url, $html, $video_url);
		}

		// если это конец строки то отдаем как домен
		if (self::_isResource($url)) {
			return Type_Preview_Parser_Default::makeDataForSiteByType(PREVIEW_TYPE_RESOURCE, $user_id, $url, $short_url, self::DOMAIN, self::_SITE_NAME, $html);
		}

		return Type_Preview_Parser_Default::makeDataForSiteByHtml($type, $user_id, $url, $short_url, self::_SITE_NAME, $html);
	}

	// проверяем, что ссылка типа profile
	protected static function _isProfile(string $type):bool {

		return parent::_isProfile($type) || $type == self::_CONTENT_TYPE_PROFILE;
	}

	// проверяем, что ссылка на пост
	protected static function _isContent(string $type):bool {

		return $type == self::_CONTENT_TYPE_WEBSITE;
	}

	// проверяем, что ссылка на видео
	protected static function _isVideo(string $type, string $video_url):bool {

		preg_match("/embed\/(.*)/i", $video_url, $matches);

		return $type == self::_VIDEO_TYPE && count($matches) > 0;
	}

	// создаем видео превью
	protected static function _makeVideoData(int $user_id, string $url, string $short_url, string $html, string $video_url):array {

		// получаем дату с PREVIEW_TYPE_VIDEO
		$data = Type_Preview_Parser_Default::makeDataForSiteByType(PREVIEW_TYPE_VIDEO, $user_id, $url, $short_url, self::DOMAIN, self::_SITE_NAME, $html);

		// добавляем поле subtype & extra
		$data["subtype"] = Type_Preview_Formatter::PREVIEW_TYPE_VIDEO_YOUTUBE;
		$data["extra"]   = [
			"video_embed_url"  => $video_url,
			"youtube_video_id" => self::_getVideoId($video_url),
		];

		return $data;
	}

	// получаем youtube_video_id
	protected static function _getVideoId(string $video_url):string {

		preg_match("/embed\/(.*)/i", $video_url, $matches);

		if (count($matches) < 1) {
			throw new ParseFatalException(__METHOD__ . ": passed incorrect video_url");
		}

		// если есть таймкод, отдаем без него
		$youtube_video_id = mb_stristr($matches[1], "?", true);
		if (mb_strlen($youtube_video_id) > 0) {
			return $youtube_video_id;
		}

		return $matches[1];
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
