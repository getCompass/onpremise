<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * задача крона
 * парсит ссылку и добавляет объект UrlPreview к сообщению
 */
class Type_Preview_Worker {

	protected Type_Logs_Cron_Parser|null $_logs = null;

	/**
	 * Выполняем задачу
	 *
	 * @param string $message_map
	 * @param int    $user_id
	 * @param array  $url_list
	 * @param string $lang
	 * @param array  $user_list
	 * @param array  $entity_info
	 * @param bool   $need_full_preview
	 *
	 * @return void
	 * @throws Domain_Conversation_Exception_Preview_IncorrectUrl
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @long
	 */
	public function doWork(string $message_map, int $user_id, array $url_list, string $lang, array $user_list, array $entity_info, bool $need_full_preview):void {

		$link_list         = [];
		$prepared_url_list = [];

		if ($url_list === [] || ($entity_info === [] && $message_map === "")) {
			return;
		}

		// если парсим превью в сообщении
		if ($message_map !== "") {

			// готовим превью для первой ссылки
			$first_url    = array_shift($url_list);
			$prepared_url = Helper_Preview::prepareUrl($first_url);

			[$preview_row, $preview_image, $first_link_list] = $this->_prepareFirstLink($user_id, $first_url, $prepared_url, $lang, $need_full_preview);

			if ($first_link_list !== []) {

				$link_list[]                      = $first_link_list;
				$prepared_url_list[$prepared_url] = $first_link_list["redirect_link"];
			}
		}

		// парсим оставшийся список ссылок
		$link_list = array_merge($link_list, $this->_parseUrlList($url_list, $prepared_url_list));

		// если массив со ссылками пустой, то завершаем работу
		if (count($link_list) < 1) {
			return;
		}

		// отправляем сокет запрос в профайл
		if ($entity_info !== []) {

			self::_attachLinkListToEntity($user_id, $user_list, $entity_info, $link_list);
			return;
		}

		// прикрепляем список ссылок к сообщению
		Helper_Preview::attachLinkList(
			$user_id,
			$message_map,
			$user_list,
			$link_list,
			$preview_row["preview_map"] ?? null,
			$preview_row["data"]["type"] ?? null,
			$preview_image ?? []);
	}

	/**
	 * Подготовить первую ссылку в сообщении
	 *
	 * @param int    $user_id
	 * @param string $original_url
	 * @param string $prepared_url
	 * @param string $lang
	 * @param bool   $need_full_preview
	 *
	 * @return array
	 * @throws Domain_Conversation_Exception_Preview_IncorrectUrl
	 * @throws ParseFatalException
	 */
	protected function _prepareFirstLink(int $user_id, string $original_url, string $prepared_url, string $lang, bool $need_full_preview):array {

		$first_url_link_list = [];
		$preview_row         = null;
		$preview_image       = [];

		if ($prepared_url !== "") {

			$this->_logs = new Type_Logs_Cron_Parser($user_id, $original_url);
			[$preview_row, $preview_image, $first_url_link_list] = $this->_createPreview($original_url, $prepared_url, $user_id, $lang, $need_full_preview);
		}

		return [$preview_row, $preview_image, $first_url_link_list];
	}

	/**
	 * Создаем превью
	 *
	 * @param string $original_url
	 * @param string $prepared_url
	 * @param int    $user_id
	 * @param string $lang
	 * @param bool   $need_full_preview
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	protected function _createPreview(string $original_url, string $prepared_url, int $user_id, string $lang, bool $need_full_preview = false):array {

		try {

			// создаем превью
			$preview_row   = Helper_Preview::createPreview($prepared_url, $user_id, $lang, $need_full_preview);
			$preview_image = $this->_getPreviewImageData($preview_row);
			$final_url     = Type_Preview_Utils::encodeUrl($preview_row["data"]["url"]);
			$link_list     = Type_Preview_Utils::makeLinkList($original_url, $final_url);
			$this->_logs->setStatus(Type_Logs_Cron_Parser::LOG_STATUS_SUCCESS)->save();
			return [$preview_row, $preview_image, $link_list];
		} catch (cs_UrlNotAllowToParse $e) {

			$link_list = Type_Preview_Utils::makeLinkList($original_url, $e->getRedirectUrl());
			return [null, [], $link_list];
		} catch (cs_UrlParseFailed $e) {

			$this->_logs->addReason($e->getErrorReason(), $e->getLastHttpCode());
			$this->_logs->setStatus($e->getParseStatus())->save();
			return $this->_createSimplePreview($original_url, $prepared_url, $user_id, $lang);
		} catch (Domain_Conversation_Exception_Preview_IncorrectUrl) {
			return [null, [], []];
		}
	}

	/**
	 * Создаем простое превью
	 *
	 * @param string $original_url
	 * @param string $prepared_url
	 * @param int    $user_id
	 * @param string $lang
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	protected function _createSimplePreview(string $original_url, string $prepared_url, int $user_id, string $lang):array {

		// пытаемся создать простое превью
		// обзяательно ловим все исключения - вдруг они выскочат
		// если не отловим - отвалится крон
		try {

			$preview_row = Helper_Preview::createPreview($prepared_url, $user_id, $lang, false);
			$final_url   = Type_Preview_Utils::encodeUrl($preview_row["data"]["url"]);
			$link_list   = Type_Preview_Utils::makeLinkList($original_url, $final_url);
			$this->_logs->setStatus(Type_Logs_Cron_Parser::LOG_STATUS_SUCCESS)->save();
			return [$preview_row, [], $link_list];
		} catch (cs_UrlParseFailed $e) {

			$this->_logs->addReason($e->getErrorReason(), $e->getLastHttpCode());
			$this->_logs->setStatus($e->getParseStatus())->save();
			$link_list = Type_Preview_Utils::makeLinkList($original_url, $original_url);
			return [null, [], $link_list];
		} catch (cs_UrlNotAllowToParse $e) {

			$link_list = Type_Preview_Utils::makeLinkList($original_url, $e->getRedirectUrl());
			return [null, [], $link_list];
		} catch (Domain_Conversation_Exception_Preview_IncorrectUrl) {
			return [null, [], []];
		}
	}

	/**
	 * Формируем информацию по превью
	 *
	 * @param array $preview_row
	 *
	 * @return array
	 */
	protected function _getPreviewImageData(array $preview_row):array {

		$output = [];

		// если имеется file_map, то добавляем размеры изображения в ответ
		if (isset($preview_row["data"]["image_file_map"]) && $preview_row["data"]["image_file_map"] !== "") {

			// достаем размеры оригинального изображения
			$width  = \CompassApp\Pack\File::getImageWidth($preview_row["data"]["image_file_map"]);
			$height = \CompassApp\Pack\File::getImageHeight($preview_row["data"]["image_file_map"]);

			// если размеры переданы, то устанавливаем их в data
			if ($width + $height > 0) {

				$output["file_width"]  = $width;
				$output["file_height"] = $height;
			}
		}

		return $output;
	}

	/**
	 * Спарсить список ссылок
	 *
	 * @param array $url_list
	 * @param array $prepared_url_list
	 *
	 * @return array
	 */
	protected function _parseUrlList(array $url_list, array $prepared_url_list = []):array {

		$link_list = [];

		// для каждой ссылки в списке
		foreach ($url_list as $url) {

			// приводим url к единому формату
			$prepared_url = Helper_Preview::prepareUrl($url);
			if ($prepared_url === "") {
				continue;
			}

			// делаем финальную ссылку
			$final_url = $this->_makeFinalUrl($prepared_url_list, $url, $prepared_url);

			// если ссылку не смогли спарсить - идем к следующей
			if ($final_url === false) {
				continue;
			}

			// собираем подготовленный список ссылок
			$link_list[]                      = Type_Preview_Utils::makeLinkList($url, $final_url);
			$prepared_url_list[$prepared_url] = $final_url;
		}

		return $link_list;
	}

	/**
	 * Финализируем ссылку
	 *
	 * @param array  $prepared_url_list
	 * @param string $url
	 * @param string $prepared_url
	 *
	 * @return string|false
	 */
	protected function _makeFinalUrl(array $prepared_url_list, string $url, string $prepared_url):string|false {

		// пробуем получить домен ссылки
		try {
			$domain_before_redirects = self::_getDomain($prepared_url);
		} catch (cs_UrlParseFailed) {

			// если не вышло получить домен пропускаем ссылку
			return false;
		}

		// получаем конечную ссылку
		try {
			$real_url = $this->_getRealUrl($prepared_url, $prepared_url_list);
		} catch (cs_UrlParseFailed) {
			$real_url = $url;
		}

		// получим домен ссылки после редиректа
		try {
			$domain_after_redirects = self::_getDomain($real_url);
		} catch (cs_UrlParseFailed) {

			// если не вышло получить домен пропускаем ссылку
			return false;
		}

		// получим финальный url
		$real_url = self::_checkDomainAfterRedirect($url, $domain_after_redirects, $domain_before_redirects, $real_url);
		return Type_Preview_Utils::encodeUrl($real_url);
	}

	// получаем конечную ссылку
	protected function _getRealUrl(string $parse_url, array $prepared_url_list):string {

		// получаем домен до редиректа
		$domain_before_redirect = self::_getDomain($parse_url);

		// если в истории уже есть ссылка
		$real_url = $prepared_url_list[$parse_url] ?? Helper_Preview::getRealUrl($parse_url);

		// получаем домен после редиректа
		$domain_after_redirect = self::_getDomain($real_url);

		// если домен не поменялся возвращем начальную ссылку
		if ($domain_before_redirect === $domain_after_redirect) {
			return $parse_url;
		}

		return $real_url;
	}

	/**
	 * Получить домен
	 *
	 * @param string $url
	 *
	 * @return string
	 * @throws cs_UrlParseFailed
	 */
	protected static function _getDomain(string $url):string {

		// получаем домен из ссылки
		$domain = parse_url($url, PHP_URL_HOST);

		// если не получилось получить домен
		if ($domain === false || is_null($domain)) {
			throw new cs_UrlParseFailed("Failed to get domain", Type_Logs_Cron_Parser::LOG_STATUS_INVALID_URL);
		}

		return $domain;
	}

	// делаем сокет запрос на php_profile чтобы добавить список ссылок
	protected static function _attachLinkListToEntity(int $user_id, array $user_list, array $entity_info, array $link_list):void {

		// делаем сокет запрос на конверсейшн что бы получить мету сингл диалога между пользователями
		[$status] = Gateway_Socket_Company::doCall("employeecard.entity.attachLinkListToEntity", [
			"link_list"        => $link_list,
			"opposite_user_id" => $entity_info["opposite_user_id"],
			"user_list"        => $user_list,
			"entity_type"      => $entity_info["entity_type"],
			"entity_id"        => $entity_info["entity_id"],
		], $user_id);

		if ($status !== "ok") {
			throw new ReturnFatalException(__METHOD__ . ": unexpected response");
		}
	}

	// проверяем сменился ли домен, если остался таким же отдаем первоначальную ссылку
	protected static function _checkDomainAfterRedirect(string $prepared_url, string $domain_after_redirects, string $domain_before_redirects, string $prepared_url_after_redirects):string {

		// сравниваем домены до и после редиректов (без поддомена "www")
		$domain_after_redirects  = preg_replace("/(www.)\b/iu", "", $domain_after_redirects);
		$domain_before_redirects = preg_replace("/(www.)\b/iu", "", $domain_before_redirects);
		if ($domain_after_redirects === $domain_before_redirects) {
			return $prepared_url;
		}

		return $prepared_url_after_redirects;
	}
}