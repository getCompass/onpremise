<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * содержит вспомогательные функции для парсинга ссылок
 */
class Type_Preview_Parser_Helper {

	// -------------------------------------------------------
	// все что касается type сайта
	// -------------------------------------------------------

	protected const _CONTENT_TYPE_PROFILE = "profile";
	protected const _CONTENT_TYPE_VIDEO   = "video";
	protected const _CONTENT_TYPE_WEBSITE = "website";

	// соотношение type получаемого с ресурса и PREVIEW_TYPE_* в коде
	protected const _CONTENT_TYPE_REL = [
		self::_CONTENT_TYPE_PROFILE => PREVIEW_TYPE_PROFILE,
		self::_CONTENT_TYPE_VIDEO   => PREVIEW_TYPE_CONTENT,
		self::_CONTENT_TYPE_WEBSITE => PREVIEW_TYPE_CONTENT,
	];

	// список регулярных выражений для поиска type
	protected const _TYPE_PATTERN_LIST = [
		0 => [
			"pattern"   => "/<meta[^>]+property=['\"]og:type['\"][^>]+content=['\"]([^\"]*?)['\"].*?>/i",
			"match_num" => 1,
		],
	];

	// получаем type страницы
	protected static function _getType(string $html):string {

		// получаем type из pattern
		return self::_tryGetMatchFromPatternList($html, self::_TYPE_PATTERN_LIST);
	}

	// ресурс типа profile
	protected static function _isProfile(string $type):bool {

		return $type == self::_CONTENT_TYPE_PROFILE;
	}

	// конвертирует type полученный с html страницы в PREVIEW_TYPE_* объявленный в модуле
	protected static function _convertContentType(string $type):int {

		$preview_type = PREVIEW_TYPE_SITE;
		if (isset(self::_CONTENT_TYPE_REL[$type])) {
			$preview_type = self::_CONTENT_TYPE_REL[$type];
		}

		return $preview_type;
	}

	// -------------------------------------------------------
	// все что касается title сайта
	// -------------------------------------------------------

	// список регулярных выражений для поиска тайтла
	protected const _TITLE_PATTERN_LIST = [
		0 => [
			"pattern"   => "/<meta[^>]+property=\"og:title\"[^>]+content=\"([^\"]*?)\".*?>/i",
			"match_num" => 1,
		],
		1 => [
			"pattern"   => "/<meta[^>]+property='og:title'[^>]+content='([^\"]*?)'.*?>/i",
			"match_num" => 1,
		],
		2 => [
			"pattern"   => "/<meta[^>]+content='[\s]*([^>]+?)'[^>]+?property='og:title'.*?>/i",
			"match_num" => 1,
		],
		3 => [
			"pattern"   => "/<meta[^>]+content=\"[\s]*([^>]+?)\"[^>]+?property=\"og:title\".*?>/i",
			"match_num" => 1,
		],
		4 => [
			"pattern"   => "/<meta[^>]+name=\"title\"[^>]+?content=\"(.*?)\">/i",
			"match_num" => 1,
		],
		5 => [
			"pattern"   => "/<meta[^>]+name='title'[^>]+?content='(.*?)'>/i",
			"match_num" => 1,
		],
		6 => [
			"pattern"   => "/<title.*?>[\s]*(.*?)[\s]*<\/title>/i",
			"match_num" => 1,
		],
	];

	// получаем title страницы
	protected static function _getTitle(string $html):string {

		// получаем title из pattern
		$title = self::_tryGetMatchFromPatternList($html, self::_TITLE_PATTERN_LIST);
		if (mb_strlen($title) < 1) {
			throw new cs_UrlParseFailed("Failed to get title", Type_Logs_Cron_Parser::LOG_STATUS_PARSE_ERROR);
		}

		$title = self::_prepareText($title, 100);

		return $title;
	}

	// -------------------------------------------------------
	// все что касается description сайта
	// -------------------------------------------------------

	// список регулярных выражений для поиска описания
	protected const _DESCRIPTION_PATTERN_LIST = [
		0 => [
			"pattern"   => "/<meta[^>]+property=\"og:description\"[^>]+content=\"([^\"]*?)\".*?>/i",
			"match_num" => 1,
		],
		1 => [
			"pattern"   => "/<meta[^>]+property='og:description'[^>]+content='([^\"]*?)'.*?>/i",
			"match_num" => 1,
		],
		2 => [
			"pattern"   => "/<meta[^>]+content=\"[\s]*([^>]+?)\"[^>]+?property=\"og:description\".*?>/i",
			"match_num" => 1,
		],
		3 => [
			"pattern"   => "/<meta[^>]+content='[\s]*([^>]+?)'[^>]+?property='og:description'.*?>/i",
			"match_num" => 1,
		],
		4 => [
			"pattern"   => "/<meta[^>]+name=['\"]description['\"][^>]+content=['\"](.*?)['\"].*?>/i",
			"match_num" => 1,
		],
		5 => [
			"pattern"   => "/<meta[^>]+content=['\"][\s]*([^>]+?)['\"][^>]+name=['\"]description['\"].*?>/i",
			"match_num" => 1,
		],
		6 => [
			"pattern"   => "/<meta[^>]+name=description[^>]+content=['\"](.*?)['\"].*?>/i",
			"match_num" => 1,
		],
	];

	// получаем description страницы
	protected static function _getDescription(string $html):string {

		// получаем description из pattern_list
		$description = self::_tryGetMatchFromPatternList($html, self::_DESCRIPTION_PATTERN_LIST);
		if (mb_strlen($description) < 1) {
			return "";
		}

		// обрабатываем текст
		$description = self::_prepareText($description, 300);

		return $description;
	}

	// -------------------------------------------------------
	// все что касается site_name сайта
	// -------------------------------------------------------

	// список регулярных выражений для поиска site_name
	protected const _SITE_NAME_PATTERN_LIST = [
		0 => [
			"pattern"   => "/<meta[^>]+property=['\"]og:site_name['\"][^>]+content=['\"]([^\"]*?)['\"].*?>/i",
			"match_num" => 1,
		],
	];

	// получить site_name
	protected static function _getSiteName(string $html, string $domain):string {

		$site_name = self::_tryGetMatchFromPatternList($html, self::_SITE_NAME_PATTERN_LIST);
		if (mb_strlen($site_name) < 1) {
			return $domain;
		}

		$site_name = self::_prepareText($site_name, 40);

		return $site_name;
	}

	// -------------------------------------------------------
	// все что касается контента с сайта
	// -------------------------------------------------------

	// список регулярных выражений для поиска ссылки на картинку
	protected const _IMAGE_PATTERN_LIST = [
		0 => [
			"pattern"   => "/<meta[^>]+property=['\"]og:image['\"][^>]+content=['\"]?(.*?)['\" ].*?>/ui",
			"match_num" => 1,
		],
		1 => [
			"pattern"   => "/<img[^>]+src=['\"]((?!data).*?)['\"].*?>/ui",
			"match_num" => 1,
		],
	];

	// функция для скачивания image preview со странички и получения image_file_map
	protected static function _tryDownloadImage(int $user_id, string $domain, string $html, string $protocol = "http"):string {

		$image_url = self::_getImageUrl($html);

		if (mb_strlen($image_url) < 1) {
			return "";
		}

		$image_url = self::_prepareContentUrl($image_url, $domain, $protocol);
		return self::_tryDownloadFile($user_id, $image_url);
	}

	// получить ссылку на preview image
	protected static function _getImageUrl(string $html):string {

		$image_url = self::_tryGetMatchFromPatternList($html, self::_IMAGE_PATTERN_LIST);
		if (mb_strlen($image_url) < 1) {
			return "";
		}

		return $image_url;
	}

	// список регулярных выражений для поиска ссылки на иконку
	protected const _FAVICON_PATTERN_LIST = [
		0 => [
			"pattern"   => "/<link[^>]+?rel=[\\'\\\"](icon)[\\'\\\"].+?href=[\\'\\\"](.*?)[\\'\\\"].*?>/ui",
			"match_num" => 2,
		],
		1 => [
			"pattern"   => "/<link[^>]+?href=[\\'\\\"]?(.*?)[\\'\\\" ] [^>]*rel=[\\'\\\"](shortcut\sicon|icon)[\\'\\\"].*?>/ui",
			"match_num" => 1,
		],
		2 => [
			"pattern"   => "/<link[^>]+?rel=[\\'\\\"](shortcut\sicon|icon)[\\'\\\"] [^>]*href=[\\'\\\"]?(.*?)[\\'\\\" ].*?>/ui",
			"match_num" => 2,
		],
		3 => [
			"pattern"   => "/<link.href=[\"](.*?)[\"].+?rel=[\"](icon)[\"].*?>/i",
			"match_num" => 1,
		],
	];

	// функция для скачивания favicon со странички и получения favicon_file_map
	protected static function _tryDownloadFavicon(int $user_id, string $domain, string $html, string $protocol = "http"):string {

		$favicon_url = self::_getFaviconUrl($html, $domain);

		$favicon_url = self::_prepareContentUrl($favicon_url, $domain, $protocol);

		return self::_tryDownloadFile($user_id, $favicon_url);
	}

	// получить favicon
	protected static function _getFaviconUrl(string $html, string $domain):string {

		$favicon_url = self::_tryGetMatchFromPatternList($html, self::_FAVICON_PATTERN_LIST);
		if (mb_strlen($favicon_url) < 1) {
			$favicon_url = "http://" . $domain . "/favicon.ico";
		}

		return $favicon_url;
	}

	// список регулярных выражений для поиска video
	protected const _VIDEO_PATTERN_LIST = [
		0 => [
			"pattern"   => "/<meta[^>]+property=['\"]og:video['\"][^>]+content=['\"]([^\"]*?)['\"].*?>/i",
			"match_num" => 1,
		],
		1 => [
			"pattern"   => "/<meta[^>]+property=['\"]og:video:url['\"][^>]+content=['\"]([^\"]*?)['\"].*?>/i",
			"match_num" => 1,
		],
	];

	/**
	 * получить ссылку на preview video
	 *
	 */
	protected static function _getVideoUrl(string $html, string $domain):string|false {

		$video_url = self::_tryGetMatchFromPatternList($html, self::_VIDEO_PATTERN_LIST);
		if (mb_strlen($video_url) < 1) {
			return false;
		}

		return self::_prepareContentUrl($video_url, $domain);
	}

	/**
	 * скачиваем файл
	 *
	 * @param int    $user_id
	 * @param string $file_url
	 *
	 * @return string
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _tryDownloadFile(int $user_id, string $file_url):string {

		$node_socket_url    = self::_getNodeForDownload();
		$company_socket_url = getConfig("SOCKET_URL")["company"];

		// отправляем ссылки на скачивание (сокет запрос на php_file_node), получаем favicon_file_map и/или preview_image_file_map
		$ar_post = [
			"file_url"    => $file_url,
			"company_id"  => COMPANY_ID,
			"company_url" => $company_socket_url,
		];

		[$status, $response] = Gateway_Socket_FileNode::doCall($node_socket_url . "api/socket/", "previews.doImageDownload", $ar_post, $user_id);

		if ($status != "ok") {

			// если нету кода ошибки
			if (!isset($response["error_code"])) {
				throw new ReturnFatalException(__CLASS__ . ": request return call not 'ok'");
			}

			// если код ошибки 10020
			if ($response["error_code"] == 10020) {
				return "";
			}
			throw new ReturnFatalException(__CLASS__ . ": request return unknown error");
		}

		return \CompassApp\Pack\File::tryDecrypt($response["file_key"]);
	}

	// получаем ссылку на ноду для сохранения файла
	protected static function _getNodeForDownload():string {

		// отправляем сокет запрос для получения url файловой ноды
		[$status, $response] = Gateway_Socket_FileBalancer::doCall("previews.getNodeForDownload", []);

		// если статус ответа не ок
		if ($status !== "ok") {
			throw new ReturnFatalException(__CLASS__ . ": request return call not 'ok'");
		}

		return $response["socket_url"];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получить match из pattern
	// @mixed - may be false если не нашли в паттерн листе
	protected static function _tryGetMatchFromPatternList(string $text, array $pattern_list):string {

		// подставляем регялярки по очереди
		foreach ($pattern_list as $pattern_row) {

			if (preg_match($pattern_row["pattern"], $text, $matches)) {

				// если дескрипшн есть, но он пустой - возвращаем false
				if (mb_strlen($matches[$pattern_row["match_num"]]) < 1) {
					continue;
				}

				return $matches[$pattern_row["match_num"]];
			}
		}

		return "";
	}

	// преобразуем спец. символы обратно в читаемые и удаляем тэги
	protected static function _prepareText(string $text, int $max_length):string {

		// преобразуем спец. символы обратно
		$text = self::_decodeEntity($text);

		// удаляем все тэги
		$text = preg_replace("/(<(\/?[^>]+)>)/u", "", $text);
		$text = trim($text);
		if (mb_strlen($text) > $max_length) {

			// обрезаем текст до 300 символов
			$text = mb_substr($text, 0, $max_length);

			// убираем лишние пробелы и заменяем последнее слово или пробел на многоточие
			$text = trim($text);
			$text = preg_replace("/\s\S*$/u", "...", $text);
		}

		if (mb_strlen($text) < 1) {
			return $text;
		}

		$text = formatString($text);
		return Type_Api_Filter::replaceEmojiWithShortName($text);
	}

	// преобразуем спец. символы обратно в html теги
	protected static function _decodeEntity(string $text):string {

		// преобразуем спец. символы обратно в html теги
		$text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, "UTF-8");

		return $text;
	}

	// подготовить url
	protected static function _prepareContentUrl(string $image_url, string $domain, string $protocol = "http"):string {

		$image_url = htmlspecialchars_decode($image_url);

		// если ссылка начинается на "//", добавляем протокол
		$image_url = preg_replace("~^(//)~u", "{$protocol}://", $image_url);

		// если ссылка относительная
		$image_url = preg_replace("~^(/|[.]/)~u", "{$protocol}://" . $domain . "/", $image_url);

		// если указан только файл
		if (!preg_match("/^{$protocol}/u", $image_url)) {
			$image_url = "{$protocol}://" . $domain . "/" . $image_url;
		}

		return formatString($image_url);
	}
}
