<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;

/**
 * класс для работы с пользовательской сессией
 */
class Type_Session_Main {

	protected const _PIVOT_COOKIE_KEY = "pivot_session_key"; // ключ, передаваемый в cookie пользователя

	// куку нужно обновлять каждый месяц
	protected const _COOKIE_NEED_REFRESH_PERIOD = DAY1 * 30;

	protected const _SESSION_STATUS_GUEST      = 1;
	protected const _SESSION_STATUS_LOGGED_IN  = 2;
	protected const _SESSION_STATUS_LOGGED_OUT = 3;

	/**
	 * проверяем что сессия существует и валидна
	 * @return int
	 * @throws \cs_SessionNotFound
	 * @throws \busException|cs_CookieIsEmpty|\returnException
	 */
	public static function getUserIdBySession():int {

		// проверяем, что у пользователя установлена кука
		$pivot_session_map = self::_getSessionMapFromCookie();

		if (mb_strlen($pivot_session_map) < 1) {
			throw new cs_CookieIsEmpty();
		}

		if (Type_Pack_PivotSession::getStatus($pivot_session_map) == self::_SESSION_STATUS_GUEST) {
			return 0;
		}

		// отдаем сессию пользователя
		$session = Gateway_Bus_PivotCache::getInfo(
			Type_Pack_PivotSession::getSessionUniq($pivot_session_map),
			Type_Pack_PivotSession::getShardId($pivot_session_map),
			Type_Pack_PivotSession::getTableId($pivot_session_map)
		);

		// если прошло 30 дней с момента обновления сессии - обновляем ее
		if ($session["refreshed_at"] + self::_COOKIE_NEED_REFRESH_PERIOD <= time()) {
			self::_refreshSession($session["user_id"], $pivot_session_map);
		}

		return $session["user_id"];
	}

	/**
	 * Получаем session_uniq из сессии
	 *
	 * @return string
	 * @throws cs_CookieIsEmpty
	 * @throws \returnException
	 */
	public static function getSessionUniqBySession():string {

		// проверяем, что у пользователя установлена кука
		$pivot_session_map = self::_getSessionMapFromCookie();
		if (mb_strlen($pivot_session_map) < 1) {
			throw new cs_CookieIsEmpty();
		}

		return Type_Pack_PivotSession::getSessionUniq($pivot_session_map);
	}

	/**
	 * логиним сессию
	 *
	 * @param int $user_id
	 *
	 * @return string
	 * @throws \queryException|cs_IncorrectSaltVersion
	 */
	public static function doLoginSession(int $user_id):string {

		$login_at = time();

		$pivot_session_map = self::startSession(self::_SESSION_STATUS_LOGGED_IN, $user_id);

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
	 * разлогиниваем сессию
	 *
	 * @param int $user_id
	 *
	 * @throws \queryException|\cs_RowIsEmpty|\returnException|cs_IncorrectSaltVersion
	 */
	public static function doLogoutSession(int $user_id):void {

		$pivot_session_map = self::_getSessionMapFromCookie();
		if (mb_strlen($pivot_session_map) < 1) {
			return;
		}
		$pivot_session_uniq = Type_Pack_PivotSession::getSessionUniq($pivot_session_map);

		// удаляем сессию из таблицы активных
		$user_active_session_row = Gateway_Db_PivotUser_SessionActiveList::getOne($user_id, $pivot_session_uniq);
		Gateway_Db_PivotHistoryLogs_SessionHistory::insert(
			$user_id,
			$pivot_session_uniq,
			self::_SESSION_STATUS_LOGGED_OUT,
			$user_active_session_row->login_at,
			time(),
			Type_Hash_UserAgent::makeHash(getUa()),
			getIp(),
			$user_active_session_row->extra
		);
		Gateway_Db_PivotUser_SessionActiveList::delete($user_id, $pivot_session_uniq);
		Type_User_ActionAnalytics::sessionEnd($user_id);

		$session_uniq = Type_Pack_PivotSession::getSessionUniq($pivot_session_map);
		Gateway_Bus_PivotCache::clearSessionCacheBySessionUniq($session_uniq);

		// отправляем задачу на разлогин
		Type_Phphooker_Main::onUserLogout($user_id, [$session_uniq]);

		self::_clearCookie();
		self::startSession();
	}

	/**
	 * Разлогинить сессии пользователя кроме текущей
	 *
	 * @param int $user_id
	 *
	 * @throws cs_IncorrectSaltVersion
	 * @throws \returnException
	 * @long
	 */
	public static function doLogoutUserSessionsExceptCurrent(int $user_id):void {

		// получаем текущую сессию, чтобы не удалять ее
		$pivot_session_map = self::_getSessionMapFromCookie();
		if (mb_strlen($pivot_session_map) < 1) {
			return;
		}
		$current_session_uniq = Type_Pack_PivotSession::getSessionUniq($pivot_session_map);

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
			$user_session_uniq_list_to_delete[]       = $session->session_uniq;
		}

		if (count($user_session_uniq_list_to_delete) === 0) {
			return;
		}

		//
		foreach ($user_session_history_list as $shard_id => $history_list) {
			Gateway_Db_PivotHistoryLogs_SessionHistory::insertArray($shard_id, $history_list);
		}
		Gateway_Db_PivotUser_SessionActiveList::deleteArray($user_id, $user_session_uniq_list_to_delete);

		// отправляем задачу на разлогин
		Type_Phphooker_Main::onUserLogout($user_id, $user_session_uniq_list_to_delete);

		// сбрасываем кэш пользователю
		Gateway_Bus_PivotCache::clearSessionCacheByUserId($user_id);
	}

	// стартуем сессию для пользователя
	public static function startSession(int $status = self::_SESSION_STATUS_GUEST, int $user_id = 0):string {

		try {

			$pivot_session_map = self::_getSessionMapFromCookie();
		} catch (ReturnFatalException) {

			return self::startSession();
		}

		if (mb_strlen($pivot_session_map) > 0) {

			if (Type_Pack_PivotSession::getStatus($pivot_session_map) == self::_SESSION_STATUS_LOGGED_IN) {

				// если пользователь залогинен, проверяем, валидная ли сессия, если нет, генерим новую
				return self::_resetIfInvalidSession($pivot_session_map);
			}
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
		self::_setCookie($pivot_session_map);
		return $pivot_session_map;
	}

	/**
	 * получить произвольный массив по ключу для текущей session_id + user_id
	 * возвращает false если запись в кэше не найдена
	 *
	 * @param string $key
	 *
	 * @return array|false
	 * @throws cs_CookieIsEmpty
	 * @mixed
	 */
	public static function getCache(string $key):bool|array {

		$val = ShardingGateway::cache()->get(self::_getKey($key));

		return $val !== false ? fromJson($val) : [];
	}

	// записать произвольный массив по ключу для текущей session_id + user_id
	public static function setCache(string $key, array $data, int $expire = 300):void {

		ShardingGateway::cache()->set(self::_getKey($key), toJson($data), $expire);
	}

	// очистить значение по ключу для текущей session_id + user_id
	public static function clearCache(string $key):void {

		ShardingGateway::cache()->delete(self::_getKey($key));
	}

	/**
	 * очищаем все сессии пользxователя
	 */
	public static function clearUserSessions(int $user_id):void {

		// инвалидируем все сессии пользователя
		Domain_User_Action_InvalidateSessions::do($user_id);

		self::_clearCookie();
	}

	/**
	 * инвалидируем все сессии пользователя
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

		self::_clearCookie();
	}

	// ------------------------------------------------------------
	// PROTECTED
	// ------------------------------------------------------------

	// функция для установки пользовательской сессии
	protected static function _setCookie(string $pivot_session_map):void {

		// если работаем из консоли
		if (isCLi()) {
			return;
		}

		$session_key = Type_Pack_PivotSession::doEncrypt($pivot_session_map);

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
	 * получаем session map из куки
	 *
	 * @return string
	 * @throws \returnException
	 * @mixed
	 */
	protected static function _getSessionMapFromCookie():string {

		// проверяем что сессии нет в куках
		if (!isset($_COOKIE[self::_PIVOT_COOKIE_KEY])) {
			return "";
		}

		$pivot_session_key = urldecode($_COOKIE[self::_PIVOT_COOKIE_KEY]);

		// проверяем, что session_key валиден
		try {
			$pivot_session_map = Type_Pack_PivotSession::doDecrypt($pivot_session_key);
		} catch (\cs_DecryptHasFailed) {

			self::_clearCookie();
			throw new ReturnFatalException("decrypt failed");
		}
		return $pivot_session_map;
	}

	// функция для очистки куки
	protected static function _clearCookie():void {

		unset($_COOKIE[self::_PIVOT_COOKIE_KEY]);

		if (!isCLi()) {
			setcookie(self::_PIVOT_COOKIE_KEY, "", -1, "/", SESSION_COOKIE_DOMAIN);
		}
	}

	// получает ключ для mCache
	protected static function _getKey(string $key):string {

		// проверяем, что у пользователя установлена кука
		$pivot_session_map = self::_getSessionMapFromCookie();

		if (mb_strlen($pivot_session_map) < 1) {

			!isCli() && throw new cs_CookieIsEmpty();
			return getDeviceId() . "_" . $key;
		}

		if (Type_Pack_PivotSession::getStatus($pivot_session_map) == self::_SESSION_STATUS_GUEST) {
			return getDeviceId() . "_" . $key;
		}

		return self::getSessionUniqBySession() . "_" . self::getUserIdBySession() . "_" . $key;
	}

	/**
	 * Перезапускаем процесс старта сессии, если она не нашлась у нас в микросервисе
	 *
	 * @param string $pivot_session_map
	 *
	 * @return string
	 */
	protected static function _resetIfInvalidSession(string $pivot_session_map):string {

		//проверяем, есть ли такая сессия у нас
		try {

			Gateway_Bus_PivotCache::getInfo(
				Type_Pack_PivotSession::getSessionUniq($pivot_session_map),
				Type_Pack_PivotSession::getShardId($pivot_session_map),
				Type_Pack_PivotSession::getTableId($pivot_session_map));
		} catch (\cs_SessionNotFound) {

			// если нет, чистим куки и перезапускаем старт сессии
			self::_clearCookie();
			return self::startSession();
		}

		return $pivot_session_map;
	}

	/**
	 * Обновить сессию у пользователя
	 *
	 * @param int    $user_id
	 * @param string $pivot_session_map
	 *
	 * @return void
	 * @throws \parseException
	 */
	protected static function _refreshSession(int $user_id, string $pivot_session_map):void {

		// ставим новую куку c той же самой сессией для пользователя
		self::_setCookie($pivot_session_map);

		// устанавливаем время обновления сессии
		$session_uniq = Type_Pack_PivotSession::getSessionUniq($pivot_session_map);
		Gateway_Db_PivotUser_SessionActiveList::set($user_id, $session_uniq, [
			"refreshed_at" => time(),
		]);

		// убираем сессию из кэша
		Gateway_Bus_PivotCache::clearSessionCacheBySessionUniq($session_uniq);
	}
}