<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Server\ServerProvider;

/**
 * класс для парсинга ссылок на youtube.com
 */
class Type_Preview_Parser_Youtube extends Type_Preview_Parser_Helper implements Type_Preview_Parser_Interface {

	public const DOMAIN             = "youtube.com";
	public const ALTERNATIVE_DOMAIN = "youtu.be";

	protected const _FAVICON_URL = "https://youtube.com/favicon.ico";

	protected const _CONTENT_TYPE_PROFILE = "yt-fb-app:channel";
	protected const _VIDEO_TYPE           = "video.other";
	protected const _SITE_NAME            = "YouTube";

	protected const _VIDEO_REGEX = "/(?:youtu\.be\/|youtube\.com(?:\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=|shorts\/)|youtu\.be\/|embed\/|v\/|m\/|watch\?(?:[^=]+=[^&]+&)*?v=))([^\"&?\/\s]{11})/";

	/**
	 * функция для определения – уместна ли переданная ссылка для парсинга конкретным парсером
	 *
	 * @return bool
	 */
	public static function isRelevantUrl(string $url):bool {

		return inHtml($url, self::DOMAIN) || inHtml($url, self::ALTERNATIVE_DOMAIN) || ServerProvider::isTest() && inHtml($url, self::_SITE_NAME);
	}

	/**
	 * функция для подготовки ссылки перед непосредственным парсингом (перед curl запросом)
	 *
	 * @return string
	 */
	public static function prepareUrl(string $url):string {

		// если это видео
		if (self::isVideoUrl($url)) {
			return self::_upgradeUrlForOembedRequest($url);
		}

		return $url;
	}

	/**
	 * является ли ссылка – ссылкой на видео
	 *
	 * @return bool
	 */
	public static function isVideoUrl(string $url):bool {

		return preg_match(self::_VIDEO_REGEX, $url) > 0;
	}

	/**
	 * апгрейдим ссылку для oembed запроса для получения информации о видео-ролике
	 *
	 * @return string
	 */
	protected static function _upgradeUrlForOembedRequest(string $url):string {

		return sprintf("https://youtube.com/oembed?url=%s", $url);
	}

	// создаем превью
	public static function makeDataFromHtml(string $user_id, string $url, string $short_url, string $html):array {

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

		return $type == self::_VIDEO_TYPE && self::isVideoUrl($video_url);
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

		preg_match(self::_VIDEO_REGEX, $video_url, $matches);

		if (count($matches) < 1) {
			throw new ParseFatalException(__METHOD__ . ": passed incorrect video_url");
		}

		return $matches[1];
	}

	// если это ресурс
	protected static function _isResource(string $url):bool {

		// если это конец строки то отдаем как домен
		preg_match("/.com(|\/|(\?|\#).*)$/i", $url, $matches);

		// парсим как ресурс
		if (count($matches) > 0 || ServerProvider::isTest()) {
			return true;
		}

		return false;
	}

	/**
	 * функция для создания превью из mime type application/json
	 *
	 * @return array
	 */
	public static function makeDataFromJson(string $user_id, string $url, string $short_url, array $content):array {

		// если это видео
		if (self::_isVideo(self::_VIDEO_TYPE, $url)) {
			return self::_makeVideoDataFromJson($user_id, $url, $content);
		}

		throw new cs_UrlParseFailed("unsupported content", Type_Logs_Cron_Parser::LOG_STATUS_PARSE_ERROR);
	}

	/**
	 * функция для создания превью на ссылку с видео из mime type application/json
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws cs_UrlParseFailed
	 * @long
	 */
	protected static function _makeVideoDataFromJson(string $user_id, string $url, array $content):array {

		// проверяем наличие обязательных полей
		if (!isset($content["title"], $content["thumbnail_url"])) {
			throw new cs_UrlParseFailed("unexpected json content", Type_Logs_Cron_Parser::LOG_STATUS_PARSE_ERROR);
		}

		// достанем ссылку на видео
		$parsed_url       = parse_url($url);
		$query_param_list = [];
		parse_str($parsed_url["query"], $query_param_list);
		if (!isset($query_param_list["url"])) {
			throw new cs_UrlParseFailed("original video url not found", Type_Logs_Cron_Parser::LOG_STATUS_PARSE_ERROR);
		}
		$original_video = $query_param_list["url"];

		// качаем favicon
		$favicon_file_map = self::_tryDownloadFile($user_id, self::_FAVICON_URL);

		// качаем превью видео
		$image_file_map = self::_tryDownloadFile($user_id, $content["thumbnail_url"]);

		// создаем превью
		$data = Type_Preview_Formatter::prepareDataForStorageByType(
			PREVIEW_TYPE_VIDEO,
			$original_video,
			self::DOMAIN,
			self::_SITE_NAME,
			$content["title"],
			$favicon_file_map,
			$image_file_map,
			""
		);

		// добавляем поле subtype & extra
		$data["subtype"] = Type_Preview_Formatter::PREVIEW_TYPE_VIDEO_YOUTUBE;
		$data["extra"]   = [
			"video_embed_url"  => $original_video,
			"youtube_video_id" => self::_getVideoId($original_video),
		];

		return $data;
	}
}
