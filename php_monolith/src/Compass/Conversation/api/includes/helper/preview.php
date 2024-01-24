<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Server\ServerProvider;

/**
 * хелпер для всего, что связано с url preview
 */
class Helper_Preview {

	protected const    _PREVIEW_EXPIRE_TIME = DAY1; // время протухания превью
	protected const    _REDIRECT_MAX_COUNT  = 10; // максимальное количество редиректов

	// mime_type картинок
	protected const _IMAGE_MIME_TYPE_LIST = [
		"image/png",
		"image/jpeg",
		"image/jpg",
		"image/x-icon",
	];

	protected const _REDIRECT_CODE = [
		300,
		301,
		302,
		303,
		307,
		308,
	];

	// http_code указывающие на недоступность
	protected const _NOT_AVAILABLE_HTTP_CODE = [
		401,
		402,
		403,
		404,
	];

	// соль для обозначения простого превью
	protected const _SIMPLE_PREVIEW_SALT = "simple";

	// подготавливает ссылку
	public static function prepareUrl(string $url):string {

		// если у переданной ссылки не указан http(s) - используем http по умолчанию
		$url = Type_Preview_Utils::addProtocol($url);
		if (preg_match("#^([\w]+://)([^/]+)(.*)$#iu", $url, $m)) {

			// пропускаем основной домен через punycode
			$m2 = Type_Preview_Punycode::encode($m[2]);
			if ($m2 === false) {
				return "";
			}

			// собираем домен обратно
			$url = $m[1] . $m2 . $m[3];
		}

		// декодируем ссылку в читаемую курлом
		// decode на случай, если придет уже перекодированная ссылка и чтобы не сломать ее через rawurlencode
		// сначала переведем обратно
		// а затем преобразуем как нам нужно
		// после rawurlencode меняем символы, присутствующие в ссылке на нормальные
		$url = rawurldecode($url);
		$url = rawurlencode($url);
		$url = str_replace(["%3A", "%2F", "%3F", "%3D", "%26", "%3B", "%40", "%23", "%2B", "%2A"], [":", "/", "?", "=", "&", ";", "@", "#", "+", "*"], $url);

		return $url;
	}

	/**
	 * Создаем превью для сайта
	 *
	 * @param string $prepared_url
	 * @param int    $user_id
	 * @param string $lang
	 * @param bool   $need_full_preview
	 *
	 * @return array
	 * @throws Domain_Conversation_Exception_Preview_IncorrectUrl
	 * @throws cs_UrlNotAllowToParse
	 * @throws cs_UrlParseFailed
	 */
	public static function createPreview(string $prepared_url, int $user_id, string $lang, bool $need_full_preview = false):array {

		$preview_map = self::_getPreviewMapFromUrl($prepared_url, $lang, $need_full_preview);

		// получаем url_preview из базы
		$preview_row = Type_Preview_Main::get($preview_map);

		// если превью уже существует (и оно не удалено) - просто отдаем его
		if (!ServerProvider::isTest() && isset($preview_row["preview_map"]) && $preview_row["is_deleted"] === 0) {

			// высчитываем время, когда должно протухнуть ревью
			$expires_at = max($preview_row["updated_at"], $preview_row["created_at"]) + self::_PREVIEW_EXPIRE_TIME;

			if ($expires_at > time()) {
				return $preview_row;
			}
		}

		$domain_before_redirects = self::_getDomain($prepared_url);

		if (!self::_isDomainAllowToParse($domain_before_redirects)) {
			throw new cs_UrlNotAllowToParse($prepared_url);
		}

		$url_preview_data = $need_full_preview
			? self::_createFullPreview($prepared_url, $domain_before_redirects, $user_id, $lang)
			: self::_createSimplePreview($prepared_url, $domain_before_redirects);

		self::_createOrUpdateRow($preview_map, $preview_row, $url_preview_data);

		return Type_Preview_Main::get($preview_map);
	}

	/**
	 * Создать полноценное превью
	 *
	 * @param string $prepared_url
	 * @param string $domain
	 * @param int    $user_id
	 * @param string $lang
	 *
	 * @return array
	 * @throws Domain_Conversation_Exception_Preview_IncorrectUrl
	 * @throws cs_UrlNotAllowToParse
	 * @throws cs_UrlParseFailed
	 */
	protected static function _createFullPreview(string $prepared_url, string $domain, int $user_id, string $lang):array {

		try {

			// инициализируем curl и устанавливаем таймаут
			$curl              = new \Curl();
			$random_user_agent = generateRandomUserAgent();
			$curl->setUserAgent($random_user_agent);
			self::_setTimeout($curl);

			// добавляем хедер с языком, на котором хотим спарсить страницу
			$curl->setAcceptLanguage($lang);

			// получаем html страницы
			$html = self::_doCurlRequest($curl, $prepared_url);
		} catch (\cs_CurlError $e) {
			throw new cs_UrlParseFailed($e->getMessage(), Type_Logs_Cron_Parser::LOG_STATUS_PARSE_ERROR);
		}

		// получаем домен после редиректов
		$prepared_url_after_redirects = $curl->getEffectiveUrl();
		$domain_after_redirects       = self::_getDomain($prepared_url_after_redirects);

		// проверяем поменялся ли домен после редиректа, если изменился отдаем последний
		$final_url = self::_checkDomainAfterRedirect($prepared_url, $domain_after_redirects, $domain, $prepared_url_after_redirects);

		// возвращаем якорь в ссылку, если пропал и получаем информацию о превью
		$final_url = self::_doAnchorFix($domain, $prepared_url, $final_url);

		return self::_makeFullPreviewData($curl, $html, $domain_after_redirects, $user_id, $final_url);
	}

	/**
	 * Сделать простое превью
	 *
	 * @param string $prepared_url
	 * @param string $domain
	 *
	 * @return array
	 * @throws Domain_Conversation_Exception_Preview_IncorrectUrl
	 * @long
	 */
	protected static function _createSimplePreview(string $prepared_url, string $domain):array {

		// если это ip адрес - достать имя сайта не получится
		if (ip2long($domain) !== false) {
			$site_name = $domain;
		} else {

			// достаем домен второго уровня
			$exploded_domain = explode(".", $domain);
			$site_name       = count($exploded_domain) >= 2 ? $exploded_domain[count($exploded_domain) - 2] : array_shift($exploded_domain);
		}

		$site_name = mb_ucfirst($site_name);

		try {
			$real_url = self::getRealUrl($prepared_url);
		} catch (cs_UrlParseFailed) {
			$real_url = $prepared_url;
		}

		$domain_after_redirects = self::_getDomain($real_url);
		$final_url              = self::_checkDomainAfterRedirect($prepared_url, $domain_after_redirects, $domain, $real_url);

		return Type_Preview_Formatter::prepareDataForStorageByType(
			PREVIEW_TYPE_SIMPLE,
			$final_url,
			$domain_after_redirects,
			$site_name,
			"",
			"",
			"",
			"",
		);
	}

	// формируем preview_map
	protected static function _getPreviewMapFromUrl(string $prepared_url, string $lang, bool $need_full_preview):string {

		// формируем preview_hash
		$salt         = $need_full_preview ? "" : self::_SIMPLE_PREVIEW_SALT;
		$preview_hash = \CompassApp\Pack\Preview::generatePreviewHash($lang, $prepared_url, $salt);

		// формируем preview_map
		$time     = time();
		$table_id = \CompassApp\Pack\Preview::getTableIdByTime($time);
		return \CompassApp\Pack\Preview::doPack($table_id, $preview_hash, $time);
	}

	/**
	 * Получаем ссылку после редиректов
	 *
	 * @param \Curl  $curl
	 * @param string $prepared_url
	 *
	 * @return string
	 * @throws \cs_CurlError
	 * @throws cs_UrlNotAllowToParse
	 * @throws cs_UrlParseFailed
	 */
	protected static function _doCurlRequest(\Curl $curl, string $prepared_url):string {

		// получаем html ссылки
		$html = $curl->get($prepared_url);
		self::_checkRequestHttpCode($curl);

		// если есть редиректы - получаем html по ссылке после редиректов
		return self::_getHtmlAfterRedirects($curl, $html);
	}

	// получаем html по ссылке после редиректов
	protected static function _getHtmlAfterRedirects(\Curl $curl, string $html):string {

		for ($i = 0; $i <= self::_REDIRECT_MAX_COUNT; $i++) {

			if ($i == self::_REDIRECT_MAX_COUNT) {
				throw new cs_UrlParseFailed("Gained max redirects", Type_Logs_Cron_Parser::LOG_STATUS_MAX_REDIRECT_GAINED);
			}

			$redirect_url = self::_getRedirectUrlIfHttp200($curl, $html);
			if ($redirect_url === false) {
				break;
			}

			// получаем ссылку после редиректов, если http код 301 или 302
			$redirect_url = self::_getRedirectedUrlIfHttp301($curl, $redirect_url);
			$domain       = self::_getDomain($redirect_url);

			// если URL не может быть спаршен, выбиваем исключение
			if (!self::_isDomainAllowToParse($domain)) {
				throw new cs_UrlNotAllowToParse($redirect_url);
			}

			$html = $curl->get($redirect_url);
		}
		return $html;
	}

	// получаем ссылку после редиректов, если вернулся 200 http код
	protected static function _getRedirectUrlIfHttp200(\Curl $curl, string $html):string|bool {

		// на случай если вернулся 401/402/403 - выходим из цикла
		if (self::_isNotAvailableHttpCode($curl->getResponseCode())) {
			return false;
		}

		// если код ответа не 200
		// на случай если вернулся 301/302/307, чтобы не выйти из цикла
		if ($curl->getResponseCode() != 200) {
			return true;
		}

		// проверяем нужно ли перейти на другую страницу
		return self::_checkHtmlRedirect($html);
	}

	// получаем redirect_url из мета тэгов страницы
	// некоторые сайты отдают 200 код и содержат в мете ссылки на дальнейшие редиректы
	// для обработки таких случаев нужен этот код
	protected static function _checkHtmlRedirect(string $html):string|bool {

		// список регулярных выражений
		// match_num - порядковый номер совпадения (ключ массива совпадений)
		$pattern_row = [
			"pattern"   => "/<[\s]*meta[\s]*http-equiv=\"?REFRESH\"?[\s]*content=\"?[0-9]*;[\s]*URL[\s]*=[\s]*([^>\"]*)\"?[\s]*[\/]?[\s]*>(?!<\/noscript>)/si",
			"match_num" => 1,
		];

		if (preg_match($pattern_row["pattern"], $html, $matches)) {

			// если редирект на туже страницу с которой пришли
			if (self::_isInfiniteLoop($matches)) {
				return false;
			}

			// возвращаем результат работы регулярки, если сработала
			return $matches[$pattern_row["match_num"]];
		}

		// если не нашли в заголовках ссылку для редиректа
		return false;
	}

	// проверяем на какую страницу ведет редирект
	protected static function _isInfiniteLoop(array $matches):bool {

		// разбиваем на 2 части
		$arr = explode("url=", $matches[0], 2);

		// в некоторых ссылках приходит URL заглавными - всю строку в нижний регистр не очень хорошо
		if (count($arr) == 1) {
			$arr = explode("URL=", $matches[0], 2);
		}

		$str = mb_substr($arr[1], 0, (mb_strlen($arr[1]) - 2));

		// если ссылки одинаковые
		if ($matches[1] == $str) {
			return true;
		}

		return false;
	}

	/**
	 * получаем ссылку после редиректов, если вернулся 301 http код
	 *
	 * @param \Curl        $curl
	 * @param string|false $redirect_url
	 *
	 * @return string
	 * @throws cs_UrlParseFailed
	 */
	protected static function _getRedirectedUrlIfHttp301(\Curl $curl, string|false $redirect_url):string {

		// если вернулся редирект http_code
		if (self::_isRedirectHttpCode($curl->getResponseCode())) {
			$redirect_url = $curl->getRedirectUrl();
		}

		if ($redirect_url === false) {
			throw new cs_UrlParseFailed("Server return error", Type_Logs_Cron_Parser::LOG_STATUS_SERVER_RESPONSE_ERROR);
		}

		return $redirect_url;
	}

	// если вдруг пропал якорь из ссылки - возвращаем
	protected static function _doAnchorFix(string $old_domain, string $old_url, string $new_url):string {

		$anchor     = parse_url($old_url, PHP_URL_FRAGMENT);
		$new_domain = self::_getDomain($new_url);

		// если домены совпали, но якорь пропал
		if ($old_domain == $new_domain && $anchor != parse_url($new_url, PHP_URL_FRAGMENT)) {
			$new_url = $new_url . "#" . $anchor;
		}

		return $new_url;
	}

	// проверяем сменился ли домен, если остался таким же или домен доверенный тогда отдаем первоначальную ссылку
	protected static function _checkDomainAfterRedirect(string $prepared_url, string $domain_after_redirects, string $domain_before_redirects, string $prepared_url_after_redirects):string {

		// если домен в доверенных то отдаем первоначальную ссылку
		if (Type_Preview_Main::isWhiteDomain($domain_before_redirects)) {
			return $prepared_url;
		}

		// сравниваем домены до и после редиректов (без поддомена "www")
		$domain_after_redirects  = preg_replace("/(www.)\b/iu", "", $domain_after_redirects);
		$domain_before_redirects = preg_replace("/(www.)\b/iu", "", $domain_before_redirects);
		if ($domain_after_redirects == $domain_before_redirects) {
			return $prepared_url;
		}

		return $prepared_url_after_redirects;
	}

	// получаем информацию по url preview
	protected static function _makeFullPreviewData(\Curl $curl, string $html, string $domain, int $user_id, string $prepared_url):array {

		$mime_type       = self::_getMimeType($curl);
		$is_need_favicon = self::_getIsNeedFaviconForDomain($domain);

		if ($mime_type === "text/html") {
			return self::_makePreviewDataMimeTypeIsHtml($curl, $html, $domain, $user_id, $prepared_url);
		}

		if (in_array($mime_type, self::_IMAGE_MIME_TYPE_LIST)) {
			return self::_makeImageDataPreview($domain, $user_id, $prepared_url, $is_need_favicon);
		}
		throw new cs_UrlParseFailed("Unhandled mime_type", Type_Logs_Cron_Parser::LOG_STATUS_PARSE_ERROR);
	}

	// получаем mime_type
	protected static function _getMimeType(\Curl $curl):string {

		// получаем заголовки
		$headers = $curl->getHeaders();

		// выбрасываем ошибку, если заголовки пустые и если нету поля content-type в headers
		self::_throwIfHeadersIsEmpty($headers);
		$headers = self::_setHeadersToLower($headers);
		self::_throwIfContentTypeNotExistInHeaders($headers);

		// если заголовок content-type является массивом
		if (is_array($headers["content-type"])) {

			// обрезаем массив
			$content_type_list = array_slice($headers["content-type"], 0, 3);

			// пытаемся получить mime_type из массива content_type
			$mime_type = self::_tryGetMimeTypeFromContentTypeLost($content_type_list);
		} else {

			// определяем mime_type
			$mime_type = explode(";", $headers["content-type"]);
			$mime_type = $mime_type[0];
		}

		return $mime_type;
	}

	// получаем флаг, нужен фавикон для домена или нет
	protected static function _getIsNeedFaviconForDomain(string $domain):bool {

		// разбиваем домен на все уровни
		$temp = explode(".", $domain);

		// если только домен 1 уровня, то выходим
		$count = count($temp);
		if ($count <= 1) {
			return false;
		}

		// переворачиваем массив для удобства
		$temp = array_reverse($temp);

		// собираем домен 2 уровня
		$domain_second = $temp[1] . "." . $temp[0];

		// получаем список доменов для которых не нужен favicon
		$no_favicon_list = getConfig("PREVIEW_NO_FAVICON_DOMAIN_LIST");

		if (in_array($domain_second, $no_favicon_list)) {
			return false;
		}
		return true;
	}

	// выбрасываем исключение, если пришел пустой массив headers
	protected static function _throwIfHeadersIsEmpty(array $headers):void {

		if (count($headers) < 1) {
			throw new cs_UrlParseFailed("Headers is empty", Type_Logs_Cron_Parser::LOG_STATUS_PARSE_ERROR);
		}
	}

	// приводим заголовки к нижнему регистру
	protected static function _setHeadersToLower(array $headers):array {

		foreach ($headers as $k => $v) {
			$headers[mb_strtolower($k)] = $v;
		}
		return $headers;
	}

	// выбрасываем исключение, если в $headers нету поля content-type
	protected static function _throwIfContentTypeNotExistInHeaders(array $headers):void {

		if (!isset($headers["content-type"])) {
			throw new cs_UrlParseFailed("Content-type not exist in Headers", Type_Logs_Cron_Parser::LOG_STATUS_SERVER_RESPONSE_ERROR);
		}
	}

	// пробуем получить mime_type из полученного content_type
	protected static function _tryGetMimeTypeFromContentTypeLost(array $content_type_list):string {

		$mime_type = "";

		// проходимся по полученному массиву
		foreach ($content_type_list as $v) {

			// определяем mime_type
			$temp = explode(";", $v);
			$temp = $temp[0];

			// если поддерживаем такой mime-type
			if ($temp === "text/html" || in_array($temp, self::_IMAGE_MIME_TYPE_LIST)) {

				$mime_type = $temp;
				break;
			}
		}

		return $mime_type;
	}

	// формируем превью сайта
	protected static function _makePreviewDataMimeTypeIsHtml(\Curl $curl, string $html, string $domain, int $user_id, string $prepared_url):array {

		// пробуем поменять кодировку на UTF-8
		$html = self::_trySetUtf8Charset($curl, $html);

		// преобразовываем домен обратно в utf-8
		$short_url = Type_Preview_Punycode::decode($domain);
		if ($short_url === false) {
			throw new cs_UrlParseFailed("Decode domain from punycode failed", Type_Logs_Cron_Parser::LOG_STATUS_PARSE_ERROR);
		}

		// парсим html и собираем preview data в зависимости от ресурса
		return self::makeData($user_id, $prepared_url, $short_url, $html);
	}

	// парсим html и собираем preview data в зависимости от ресурса
	public static function makeData(string $user_id, string $url, string $short_url, string $html):array {

		// вычисляем ресурс по доменному имени
		if (inHtml($short_url, Type_Preview_Parser_Instagram::DOMAIN) || ServerProvider::isTest() && inHtml($url, "instagram")) {
			return Type_Preview_Parser_Instagram::makeData($user_id, $url, $short_url, $html);
		}
		if (inHtml($short_url, Type_Preview_Parser_Youtube::DOMAIN) || ServerProvider::isTest() && inHtml($url, "youtube")) {
			return Type_Preview_Parser_Youtube::makeData($user_id, $url, $short_url, $html);
		}
		if (inHtml($short_url, Type_Preview_Parser_Facebook::DOMAIN) || ServerProvider::isTest() && inHtml($url, "facebook")) {
			return Type_Preview_Parser_Facebook::makeData($user_id, $url, $short_url, $html);
		}
		if (inHtml($short_url, Type_Preview_Parser_Mail::DOMAIN) || ServerProvider::isTest() && inHtml($url, "mail")) {
			return Type_Preview_Parser_Mail::makeData($user_id, $url, $short_url, $html);
		}
		if (inHtml($short_url, Type_Preview_Parser_Habrahabr::DOMAIN) || ServerProvider::isTest() && inHtml($url, "habrahabr")) {
			return Type_Preview_Parser_Habrahabr::makeData($user_id, $url, $short_url, $html);
		}
		if (inHtml($short_url, Type_Preview_Parser_Ok::DOMAIN) || ServerProvider::isTest() && inHtml($url, "ok")) {
			return Type_Preview_Parser_Ok::makeData($user_id, $url, $short_url, $html);
		}
		// добавляем слэш в конце, чтобы после домена верхнего уровня не было ничего
		if (str_contains($url, Type_Preview_Parser_Compass::DOMAIN)
			|| preg_match("/getcompass.(ru|com)(\/|$)/iu", $url)
			|| preg_match(sprintf("/%s(\/|$)/iu", str_replace("/", "\/", PUBLIC_ADDRESS_GLOBAL)), $url)) {
			return Type_Preview_Parser_Compass::makeData($user_id, $url, $short_url, $html);
		}

		// собираем по дефолту
		return Type_Preview_Parser_Default::makeData($user_id, $url, $short_url, $html);
	}

	// пробуем поменять кодировку html на utf-8
	protected static function _trySetUtf8Charset(\Curl $curl, string $html):string {

		// меняем кодировку на utf-8, чтобы определить кодировку и не сломать кириллицу
		$temp = mb_convert_encoding($html, "utf-8");

		// получаем кодировку веб-страницы
		$charset = self::_getCharset($temp, $curl);

		// если кодировка не UTF-8
		if (mb_strtoupper($charset) !== "UTF-8") {

			try {

				// пробуем перекодировать в UTF-8
				$html = iconv($charset, "UTF-8", $html);
			} catch (\Error) {
				throw new cs_UrlParseFailed("Set charset to utf-8 failed", Type_Logs_Cron_Parser::LOG_STATUS_PARSE_ERROR);
			}
		}

		return $html;
	}

	// получаем кодировку
	protected static function _getCharset(string $html, \Curl $curl):string {

		// пробуем найти кодировку
		if (preg_match("/<meta[^>]+?http-equiv=['\"]Content-Type['\"].*?content=['\"].*charset=([0-9a-zA-Z-]+).*['\"]/ui", $html, $matches)) {
			return str_replace("'", "", str_replace("\"", "", $matches[1]));
		}

		// если кодировка все еще неизвестна
		if (preg_match("/<meta[^>]+?charset=['\"].*?([0-9a-zA-Z-]+).*['\"]/ui", $html, $matches)) {
			return str_replace("'", "", str_replace("\"", "", $matches[1]));
		}

		if (isset($curl->curlinfo->content_type) && preg_match("/charset=([0-9a-zA-Z-]+)/iu", $curl->getContentType(), $matches)) {
			return str_replace("'", "", str_replace("\"", "", $matches[1]));
		}

		// если не известна кодировка, то возвращаем по умолчанию
		return "UTF-8";
	}

	// формируем превью картинки
	protected static function _makeImageDataPreview(string $domain, int $user_id, string $prepared_url, bool $is_need_favicon):array {

		// получаем file_map изображения
		$image_file_map = self::_tryDownloadFile($user_id, $prepared_url);

		// если пустой file_map то смысла делать это превью - нет
		if ($image_file_map == "") {
			throw new cs_UrlParseFailed("Failed to get image", Type_Logs_Cron_Parser::LOG_STATUS_PARSE_ERROR);
		}

		$favicon_file_map = "";
		if ($is_need_favicon) {

			// получаем file_map иконки
			$favicon_url      = "http://" . $domain . "/favicon.ico";
			$favicon_file_map = self::_tryDownloadFile($user_id, $favicon_url);
		}

		return Type_Preview_Formatter::makeImageData(rawurldecode($prepared_url), $domain, $domain, $image_file_map, $favicon_file_map);
	}

	// обновляем запись в базе или создаем новую
	protected static function _createOrUpdateRow(string $preview_map, array $url_preview, array $url_preview_data):void {

		// если превью есть в базе
		if (isset($url_preview["preview_map"])) {

			// обновляем информацию о превью в базе
			Type_Preview_Main::set($preview_map, [
				"is_deleted" => 0,
				"updated_at" => time(),
				"data"       => $url_preview_data,
			]);
			return;
		}

		// сохраняем информацию о превью в базе
		Type_Preview_Main::create($preview_map, $url_preview_data);
	}

	/**
	 * Прикрепляем ссылки к сообщению
	 *
	 * @param int         $user_id
	 * @param string      $message_map
	 * @param array       $users
	 * @param array       $link_list
	 * @param string|null $preview_map
	 * @param int|null    $preview_type
	 * @param array       $preview_image
	 *
	 * @return void
	 */
	public static function attachLinkList(int   $user_id, string $message_map, array $users, array $link_list, string $preview_map = null, int $preview_type = null,
							  array $preview_image = []):void {

		// прикрепляем preview и список ссылок к сообщению и шлем событие всем пользователям, переданным в крон
		$dynamic = Type_Preview_Main::attachToMessage($user_id, $message_map, $link_list, $users, $preview_map, $preview_type, $preview_image);
		self::_sendWS($users, $message_map, $link_list, $dynamic->messages_updated_version, $preview_map, $preview_type, $preview_image);
	}

	// шлет ws событие
	protected static function _sendWS(array $user_list, string $message_map, array $link_list, int $messages_updated_version, string $preview_map = null, int $preview_type = null, array $preview_image = []):void {

		// формируем список пользователей, которым нужно отправлять ws-эвент
		$talking_user_list = Type_Conversation_Meta_Users::getTalkingUserList($user_list);

		// достаем conversation_map из сообщения, чтобы отдать по ws
		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);

		// не даем клиентам информации о простых превью - они не умеют их отображать
		if ($preview_type === PREVIEW_TYPE_SIMPLE) {

			$preview_map   = null;
			$preview_type  = null;
			$preview_image = [];
		}

		foreach ($link_list as $key => $link) {

			if (isset($link["original_link"])) {
				$link_list[$key]["redirect_link"] = $link["original_link"];
			}
		}

		// отправляем ws событие всем пользователям, переданным в крон
		Gateway_Bus_Sender::conversationMessageLinkDataChanged(
			$talking_user_list,
			$conversation_map,
			$message_map,
			$link_list,
			$messages_updated_version,
			$preview_map,
			$preview_type,
			$preview_image
		);
	}

	// получаем конечную ссылку после редиректов
	public static function getRealUrl(string $url):string {

		$domain = self::_getDomain($url);
		if (!self::_isDomainAllowToParse($domain)) {
			return $url;
		}

		try {

			// в случае редиректа
			$curl = self::_doFirstRequest($url);
			self::_checkRequestHttpCode($curl);
			for ($i = 0; $i < self::_REDIRECT_MAX_COUNT; $i++) {

				// если код ответа 200
				if ($curl->getResponseCode() == 200) {
					return $url;
				}

				// если вернулся редирект http_code
				if (self::_isRedirectHttpCode($curl->getResponseCode())) {

					$redirect_url = $curl->getRedirectUrl();

					// если не пришла ссылка редиректа
					if (mb_strlen($redirect_url) < 1) {
						return $url;
					}

					$curl->get($redirect_url);

					// получаем новый ресурс по адресу редиректа
					$url = $curl->getEffectiveUrl();

					$domain = self::_getDomain($url);
					if (!self::_isDomainAllowToParse($domain)) {
						return $url;
					}

					continue;
				}

				throw new cs_UrlParseFailed("Failed to get redirect", Type_Logs_Cron_Parser::LOG_STATUS_SERVER_RESPONSE_ERROR);
			}
		} catch (\cs_CurlError $e) {
			throw new cs_UrlParseFailed($e->getMessage(), Type_Logs_Cron_Parser::LOG_STATUS_PARSE_ERROR);
		}

		throw new cs_UrlParseFailed("Failed to get real url", Type_Logs_Cron_Parser::LOG_STATUS_SERVER_RESPONSE_ERROR);
	}

	// -------------------------------------------------------
	// PROTECTED FUNCTIONS
	// -------------------------------------------------------

	// получаем домен
	protected static function _getDomain(string $url):string {

		// получаем домен из ссылки
		$domain = parse_url($url, PHP_URL_HOST);

		// если не получилось получить домен
		if ($domain === false || is_null($domain)) {

			throw new Domain_Conversation_Exception_Preview_IncorrectUrl("Failed to get domain from url: {$url}");
		}

		return $domain;
	}

	// инициализируем curl и получаем инфо по ссылке
	protected static function _doFirstRequest(string $url):\Curl {

		// инициализируем curl и устанавливаем таймаут с User Agent
		$curl = new \Curl();
		self::_setTimeout($curl);

		$random_user_agent = generateRandomUserAgent();
		$curl->setUserAgent($random_user_agent);

		$curl->get($url);

		return $curl;
	}

	// проверяем, можно ли парсить домен
	protected static function _isDomainAllowToParse(string $domain):bool {

		// проверяем, что домен не в списке исключений, иначе
		// проверяем наличие домена в белом или черном списке
		if (Type_Preview_Main::isDomainExcluded($domain)) {
			return false;
		}

		if (!Type_Preview_Main::isWhiteDomain($domain) && Type_Preview_Main::isBlackDomain($domain)) {
			return false;
		}

		return true;
	}

	// устанавливаем timeout для курла
	protected static function _setTimeout(\Curl $curl):void {

		$curl->setTimeout(2);

		if (ServerProvider::isTest()) {
			$curl->setTimeout(10);
		}
	}

	// проверяем http_code запроса
	protected static function _checkRequestHttpCode(\Curl $curl):void {

		// если не получили ответа
		if ($curl->getResponseCode() == 0) {
			throw new cs_UrlParseFailed("No response from site", Type_Logs_Cron_Parser::LOG_STATUS_PARSE_ERROR);
		}
	}

	// проверяем, является ли http_code кодом редиректа
	protected static function _isRedirectHttpCode(int $http_code):bool {

		// если совпал редирект
		if (in_array($http_code, self::_REDIRECT_CODE)) {
			return true;
		}

		return false;
	}

	// проверяем, является ли http_code кодом указываеющим на недоступность
	protected static function _isNotAvailableHttpCode(int $http_code):bool {

		// если совпал редирект
		if (in_array($http_code, self::_NOT_AVAILABLE_HTTP_CODE)) {
			return true;
		}

		return false;
	}

	/**
	 * скачиваем файл
	 *
	 * @param int    $user_id
	 * @param string $file_url
	 *
	 * @return string
	 * @throws ReturnFatalException
	 * @throws \paramException
	 * @throws \parseException
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

		if ($status !== "ok") {

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
}
