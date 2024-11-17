<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\EmptyAuthorizationException;
use BaseFrame\Exception\Request\InvalidAuthorizationException;

/**
 * класс для работы с пользовательской сессией
 */
class Type_Session_Main {

	protected const _TOKEN_HEADER_UNIQUE = "pivot_authorization_token";                            // ключ, передаваемый в заголовке пользователя
	protected const _PIVOT_COOKIE_KEY    = "pivot_session_key";                                    // ключ, передаваемый в cookie пользователя
	protected const _HEADER_AUTH_TYPE    = \BaseFrame\Http\Header\Authorization::AUTH_TYPE_BEARER; // тип токена для запроса

	// куку нужно обновлять каждый месяц
	protected const _COOKIE_NEED_REFRESH_PERIOD = DAY1 * 30;

	protected const _SESSION_STATUS_GUEST      = 1;
	protected const _SESSION_STATUS_LOGGED_IN  = 2;
	protected const _SESSION_STATUS_LOGGED_OUT = 3;

	/**
	 * Проверяет, что сессия существует и валидна.
	 *
	 * @throws \BaseFrame\Exception\Request\EmptyAuthorizationException
	 * @throws \BaseFrame\Exception\Request\InvalidAuthorizationException
	 */
	public static function getUserIdBySession():int {

		// проверяем, что у пользователя установлена кука
		$pivot_session_map = self::_getSessionMap();

		if (Type_Pack_PivotSession::getStatus($pivot_session_map) == self::_SESSION_STATUS_GUEST) {
			return 0;
		}

		try {

			// отдаем сессию пользователя
			$session = Gateway_Bus_PivotCache::getInfo(
				Type_Pack_PivotSession::getSessionUniq($pivot_session_map),
				Type_Pack_PivotSession::getShardId($pivot_session_map),
				Type_Pack_PivotSession::getTableId($pivot_session_map)
			);
		} catch (\cs_SessionNotFound $e) {
			throw new \BaseFrame\Exception\Request\InvalidAuthorizationException($e->getMessage());
		}

		// если прошло 30 дней с момента обновления сессии - обновляем ее
		if ($session["refreshed_at"] + self::_COOKIE_NEED_REFRESH_PERIOD <= time()) {
			self::_refreshSession($session["user_id"], $pivot_session_map);
		}

		return $session["user_id"];
	}

	/**
	 * Получаем session_uniq из сессии
	 *
	 * @throws \BaseFrame\Exception\Request\EmptyAuthorizationException
	 * @throws \BaseFrame\Exception\Request\InvalidAuthorizationException
	 */
	public static function getSessionUniqBySession():string {

		return Type_Pack_PivotSession::getSessionUniq(static::_getSessionMap());
	}

	/**
	 * Аутентифицируем сессию.
	 */
	public static function doLoginSession(int $user_id):string {

		$login_at = time();

		$pivot_session_map  = self::startSession(self::_SESSION_STATUS_LOGGED_IN, $user_id);
		$pivot_session_uniq = Type_Pack_PivotSession::getSessionUniq($pivot_session_map);

		// делаем записи в таблицах о том что сессия залогинена
		Gateway_Db_PivotUser_SessionActiveList::insert(
			$user_id,
			$pivot_session_uniq,
			Type_Pack_PivotSession::getCreateTime($pivot_session_map),
			$login_at,
			$login_at,
			$login_at,
			Type_Hash_UserAgent::makeHash(getUa()),
			getIp(),
			[]
		);

		return $pivot_session_map;
	}

	/**
	 * Инвалидируем сессию.
	 */
	public static function doLogoutSession(int $user_id):void {

		try {

			$pivot_session_map  = self::_getSessionMap();
			$pivot_session_uniq = Type_Pack_PivotSession::getSessionUniq($pivot_session_map);
		} catch (\BaseFrame\Exception\Request\EmptyAuthorizationException|\BaseFrame\Exception\Request\InvalidAuthorizationException) {
			return;
		}

		try {

			// удаляем сессию из таблицы активных и фиксируем событие в истории
			$user_active_session_row = Gateway_Db_PivotUser_SessionActiveList::getOne($user_id, $pivot_session_uniq);
			Gateway_Db_PivotHistoryLogs_SessionHistory::insert(
				$user_id, $pivot_session_uniq, self::_SESSION_STATUS_LOGGED_OUT, $user_active_session_row->login_at,
				time(), Type_Hash_UserAgent::makeHash(getUa()), getIp(), $user_active_session_row->extra
			);
		} catch (\cs_RowIsEmpty) {

			// это какая-то внештатная ситуация, такого не должно происходить
			// но останавливать процесс разлогина не нужно, м.б. как-то в лог откинуть
		}

		// удаляем сессию и добавляем задачу на разлогин
		Gateway_Db_PivotUser_SessionActiveList::delete($user_id, $pivot_session_uniq);
		Gateway_Bus_PivotCache::clearSessionCacheBySessionUniq($pivot_session_uniq);
		Type_Phphooker_Main::onUserLogout($user_id, [$pivot_session_uniq]);

		Type_User_ActionAnalytics::sessionEnd($user_id);

		self::_clearRequestAuthData();
		self::startSession();
	}

	/**
	 * Инвалидируем все сессии, кроме текущей.
	 */
	public static function doLogoutUserSessionsExceptCurrent(int $user_id):void {

		try {

			$pivot_session_map    = self::_getSessionMap();
			$current_session_uniq = Type_Pack_PivotSession::getSessionUniq($pivot_session_map);
		} catch (EmptyAuthorizationException|InvalidAuthorizationException) {
			return;
		}

		// получаем список активных сессий
		$session_list = Gateway_Db_PivotUser_SessionActiveList::getActiveSessionList($user_id);

		// формируем массивы для удаления и истории
		$user_session_history_list        = [];
		$user_session_uniq_list_to_delete = [];

		foreach ($session_list as $session) {

			if ($session->session_uniq === $current_session_uniq) {
				continue;
			}

			$logout_at  = time();
			$shard_year = Gateway_Db_PivotHistoryLogs_Main::getShardIdByTime($logout_at);
			if (!isset($user_session_history_list[$shard_year])) {
				$user_session_history_list[$shard_year] = [];
			}

			$user_session_history_list[$shard_year][] = [
				"session_uniq" => $session->session_uniq,
				"user_id"      => $user_id,
				"status"       => self::_SESSION_STATUS_LOGGED_OUT,
				"login_at"     => $session->login_at,
				"logout_at"    => $logout_at,
				"ua_hash"      => Type_Hash_UserAgent::makeHash(getUa()),
				"ip_address"   => getIp(),
				"extra"        => $session->extra,
			];

			$user_session_uniq_list_to_delete[] = $session->session_uniq;
		}

		if (count($user_session_uniq_list_to_delete) === 0) {
			return;
		}

		foreach ($user_session_history_list as $shard_id => $history_list) {
			Gateway_Db_PivotHistoryLogs_SessionHistory::insertArray($shard_id, $history_list);
		}

		Gateway_Db_PivotUser_SessionActiveList::deleteArray($user_id, $user_session_uniq_list_to_delete);

		// отправляем задачу на разлогин и сбрасываем кэш пользователю
		Type_Phphooker_Main::onUserLogout($user_id, $user_session_uniq_list_to_delete);
		Gateway_Bus_PivotCache::clearSessionCacheByUserId($user_id);
	}

	/**
	 * Инвалидирует все сессии пользователя, включая сессии в компаниях.
	 */
	public static function clearAllUserPivotAndCompanySessions(int $user_id):void {

		Type_System_Admin::log("user-kicker", "удаляю активные сессии пользователя {$user_id}");

		// сбрасываем сессии
		$session_list      = Gateway_Db_PivotUser_SessionActiveList::getActiveSessionList($user_id);
		$session_uniq_list = array_column($session_list, "session_uniq");

		// удаляем сессии
		Gateway_Db_PivotUser_SessionActiveList::deleteArray($user_id, $session_uniq_list);

		// отправляем задачу на разлогин
		Type_Phphooker_Main::onUserLogout($user_id, $session_uniq_list);

		// не забываем сбросить кэш
		Gateway_Bus_PivotCache::clearSessionCacheByUserId($user_id);

		self::_clearRequestAuthData();
	}

	/**
	 * Возвращает данные по ключу для текущей session_id + user_id.
	 * <b>С кэшем нельзя работать, если у запроса нет данных авторизации.<b>
	 */
	public static function getCache(string $key):array {

		$val = ShardingGateway::cache()->get(self::_getCacheKey($key));

		return $val !== false ? fromJson($val) : [];
	}

	/**
	 * Записывает данные по ключу для текущей session_id + user_id.
	 * <b>С кэшем нельзя работать, если у запроса нет данных авторизации.<b>
	 */
	public static function setCache(string $key, array $data, int $expire = 300):void {

		ShardingGateway::cache()->set(self::_getCacheKey($key), toJson($data), $expire);
	}

	/**
	 * Очищает данные по ключу для текущей session_id + user_id.
	 * <b>С кэшем нельзя работать, если у запроса нет данных авторизации.<b>
	 */
	public static function clearCache(string $key):void {

		ShardingGateway::cache()->delete(self::_getCacheKey($key));
	}

	/**
	 * Инициализирует новую или возвращает актуальную сессию для пользователя.
	 */
	public static function startSession(int $status = self::_SESSION_STATUS_GUEST, int $user_id = 0):string {

		try {

			$pivot_session_map = static::_getSessionMap();

			// если пользователь залогинен, проверяем, валидная ли сессия, если нет, генерим новую
			if (Type_Pack_PivotSession::getStatus($pivot_session_map) === static::_SESSION_STATUS_LOGGED_IN) {
				return static::_resetIfInvalidSession($pivot_session_map);
			}
		} catch (EmptyAuthorizationException|InvalidAuthorizationException) {

			// если активной сессии нет или она некорректна,
			// это нормально, просто запускаем новую гостевую
		}

		$session_uniq = generateUUID();
		$time         = time();

		// формируем pivot_session_key
		$pivot_session_map = Type_Pack_PivotSession::doPack(
			$session_uniq,
			Type_Pack_PivotSession::getShardIdByUserId($user_id),
			Type_Pack_PivotSession::getTableIdByUserId($user_id),
			$time,
			$status
		);

		// ставим куки
		static::_setClientAuthData(Type_Pack_PivotSession::doEncrypt($pivot_session_map));
		return $pivot_session_map;
	}

	/**
	 * Пытается получить session_map из данных запроса.
	 *
	 * @throws \BaseFrame\Exception\Request\EmptyAuthorizationException
	 * @throws \BaseFrame\Exception\Request\InvalidAuthorizationException
	 */
	protected static function _getSessionMap():string {

		$pivot_session_key = false;

		// получаем наличие заголовка и ключ из заголовка
		// далее возможно процесс установки заголовка значением из кук
		[$has_header, $header_pivot_session_token] = static::tryGetSessionMapFromAuthorizationHeader();

		// если в заголовке нет или токен заголовка
		// пустой, то достаем сессию из куки
		if ($has_header === false || $header_pivot_session_token === "") {
			$pivot_session_key = static::tryGetSessionMapFromCookie();
		}

		// если есть токен из заголовка, но нет ключа из кук, то
		// используем токен из заголовка как значение ключа
		if ($pivot_session_key === false && $header_pivot_session_token !== "") {
			$pivot_session_key = $header_pivot_session_token;
		}

		// если ничего не нашли, то сдаемся и считаем, что
		// клиент е прислал никаких данных авторизации запроса
		if ($pivot_session_key === false) {
			throw new \BaseFrame\Exception\Request\EmptyAuthorizationException("auth data not found");
		}

		// если ключ пустой, то и смысла его пытаться декодировать нет,
		// такое будет, если к нам пришел None заголовок и кука пустая
		if ($pivot_session_key === "") {
			throw new \BaseFrame\Exception\Request\EmptyAuthorizationException("auth data empty");
		}

		// проверяем, что session_key валиден
		try {
			$pivot_session_map = Type_Pack_PivotSession::doDecrypt($pivot_session_key);
		} catch (\cs_DecryptHasFailed) {

			// если клиенту нужно почистить куку (чистим для старых клиентов
			// или если клиент зачем-то захотел, чтобы мы чистили ее вне start)
			if (\BaseFrame\Http\Header\AuthorizationControl::parse()::needClear()) {
				self::_clearRequestAuthData();
			}

			throw new \BaseFrame\Exception\Request\InvalidAuthorizationException("decrypt failed");
		}

		return $pivot_session_map;
	}

	/**
	 * Пытается получить токен из заголовка авторизации.
	 */
	public static function tryGetSessionMapFromAuthorizationHeader():array {

		// получаем заголовок авторизации
		$auth_header = \BaseFrame\Http\Header\Authorization::parse();

		// заголовка авторизации в запросе нет
		if ($auth_header === false) {
			return [false, ""];
		}

		// заголовок есть, но он пустой, т.е. клиент поддерживает
		// авторизацию через заголовок, но еще не получал токен
		if ($auth_header->isNone()) {
			return [true, ""];
		}

		// заголовок есть, но он имеет некорректный формат
		if (!$auth_header->isCorrect()) {
			return [false, ""];
		}

		// заголовок есть, он корректный, но предназначен не для этого
		// модуля/сервиса, считаем, что заголовка нет в таком случае
		if ($auth_header->getType() !== static::_HEADER_AUTH_TYPE) {
			return [false, ""];
		}

		return [true, base64_decode($auth_header->getToken())];
	}

	/**
	 * Пытается получить токен из cookie.
	 */
	public static function tryGetSessionMapFromCookie():string|false {

		// проверяем что сессии нет в куках
		if (!isset($_COOKIE[self::_PIVOT_COOKIE_KEY])) {
			return false;
		}

		return urldecode($_COOKIE[self::_PIVOT_COOKIE_KEY]);
	}

	/**
	 * Устанавливает клиенту данные авторизации.
	 */
	protected static function _setClientAuthData(string $pivot_session_key):void {

		static::setHeaderAction($pivot_session_key);
		static::_setCookie($pivot_session_key);
	}

	/**
	 * Устанавливает данные, которые клиент клиент будет использовать для авторизации в дальнейшем.
	 *
	 * <b>!!! Функция имеет public уровень только для миграции куки -> заголовок, в остальных
	 *  случая публичное использование функции запрещено !!!<b>
	 */
	public static function setHeaderAction(string $session_key):void {

		$auth_item = Type_Session_Main::makeAuthenticationItem($session_key);
		\BaseFrame\Http\Authorization\Data::inst()->set($auth_item["unique"], $auth_item["token"]);
	}

	/**
	 * Устанавливает cookie авторизации клиенту.
	 */
	protected static function _setCookie(string $session_key):void {

		// если работаем из консоли
		if (isCLi()) {
			return;
		}

		// устанавливаем session_key для пользователя
		setcookie(
			self::_PIVOT_COOKIE_KEY,
			urlencode($session_key),
			time() + DAY1 * 360,
			"/",
			SESSION_COOKIE_DOMAIN,
			false,
			false
		);
	}

	/**
	 * Сбрасывает данные авторизации для клиента.
	 */
	protected static function _clearRequestAuthData():void {

		// готовим сброс заголовка
		static::_clearHeader();

		// сбрасываем авторизацию в куках,
		// даже если клиент пользовался заголовком
		static::_clearCookie();
	}

	/**
	 * Готовит данные для action сброса заголовка авторизации.
	 */
	protected static function _clearHeader():void {

		// инвалидируем заголовок
		\BaseFrame\Http\Header\Authorization::invalidate();

		// устанавливаем данные для authorization action
		\BaseFrame\Http\Authorization\Data::inst()->drop(static::_TOKEN_HEADER_UNIQUE);
	}

	/**
	 * Очищает cookie авторизации клиенту.
	 */
	protected static function _clearCookie():void {

		unset($_COOKIE[self::_PIVOT_COOKIE_KEY]);

		if (!isCLi()) {
			setcookie(self::_PIVOT_COOKIE_KEY, "", -1, "/", SESSION_COOKIE_DOMAIN);
		}
	}

	/**
	 * Получает ключ для mCache.
	 */
	protected static function _getCacheKey(string $key):string {

		try {

			$pivot_session_map = self::_getSessionMap();
			$user_id           = self::getUserIdBySession();
			$session_uniq      = self::getSessionUniqBySession();
		} catch (EmptyAuthorizationException|InvalidAuthorizationException $e) {

			if (isCLi()) {
				return getDeviceId() . "_" . $key;
			}

			throw new \BaseFrame\Exception\Domain\ReturnFatalException($e->getMessage());
		}

		if (Type_Pack_PivotSession::getStatus($pivot_session_map) == self::_SESSION_STATUS_GUEST) {
			return $session_uniq . "_" . $key;
		}

		return $session_uniq . "_" . $user_id . "_" . $key;
	}

	/**
	 * Инвалидируем сессию, если считаем ее истекшей/некорректной.
	 */
	protected static function _resetIfInvalidSession(string $pivot_session_map):string {

		// проверяем, есть ли такая сессия у нас
		try {

			Gateway_Bus_PivotCache::getInfo(
				Type_Pack_PivotSession::getSessionUniq($pivot_session_map),
				Type_Pack_PivotSession::getShardId($pivot_session_map),
				Type_Pack_PivotSession::getTableId($pivot_session_map)
			);
		} catch (\cs_SessionNotFound) {

			// если нет, чистим куки и перезапускаем старт сессии
			self::_clearRequestAuthData();
			return self::startSession();
		}

		return $pivot_session_map;
	}

	/**
	 * Обновляет сессию пользователя.
	 */
	protected static function _refreshSession(int $user_id, string $pivot_session_map):void {

		// ставим новую куку c той же самой сессией для пользователя
		self::_setClientAuthData(Type_Pack_PivotSession::doEncrypt($pivot_session_map));

		// устанавливаем время обновления сессии
		$session_uniq = Type_Pack_PivotSession::getSessionUniq($pivot_session_map);
		Gateway_Db_PivotUser_SessionActiveList::set($user_id, $session_uniq, [
			"refreshed_at" => time(),
		]);

		// убираем сессию из кэша
		Gateway_Bus_PivotCache::clearSessionCacheBySessionUniq($session_uniq);
	}

	/**
	 * Формирует объект с данными авторизации клиента.
	 */
	public static function makeAuthenticationItem(string $session_key):array {

		return [
			"unique" => static::_TOKEN_HEADER_UNIQUE,
			"token"  => sprintf("%s %s", static::_HEADER_AUTH_TYPE, base64_encode($session_key))
		];
	}
}