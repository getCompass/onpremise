<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс для работы с пользовательской сессией
 */
class Type_Session_Main {

	protected const _COMPANY_COOKIE_KEY = COMPANY_ID . "_company_session_key"; // ключ, передаваемый в cookie пользователя

	protected const _SESSION_STATUS_LOGGED_IN  = 2;
	protected const _SESSION_STATUS_LOGGED_OUT = 3;

	/**
	 * проверяем что сессия существует и валидна
	 *
	 * @throws \busException
	 * @throws \cs_SessionNotFound
	 */
	public static function getSession():array {

		// проверяем, что у пользователя установлена кука
		$cloud_session_uniq = self::_getCloudSessionUniqFromCookie();
		if ($cloud_session_uniq === false) {
			return [0, false, false, false];
		}

		// отдаем сессию пользователя
		[$member] = Gateway_Bus_CompanyCache::getSessionInfo($cloud_session_uniq);
		return [$member->user_id, $cloud_session_uniq, $member->role, $member->permissions];
	}

	/**
	 * логиним сессию
	 *
	 * @throws \queryException
	 */
	public static function doLoginSession(int $user_id, string $user_company_session_token):string {

		$login_at           = time();
		$cloud_session_uniq = generateUUID();

		// шифруем сессию
		$session_key = Type_Pack_CompanySession::doEncrypt(Type_Pack_CompanySession::doPack($cloud_session_uniq));

		// ставим куки
		self::_setCookie($session_key);

		// формируем экстру сессии
		$extra = Domain_User_Entity_ActiveSession_Extra::initExtra();

		// делаем записи в таблицах о том что сессия залогинена
		Gateway_Db_CompanyData_SessionActiveList::insert(
			$cloud_session_uniq,
			$user_id,
			$user_company_session_token,
			time(),
			$login_at,
			$login_at,
			getIp(),
			getUa(),
			$extra
		);

		return $session_key;
	}

	/**
	 * разлогиниваем сессию
	 *
	 * @throws \busException
	 * @throws \returnException
	 */
	public static function doLogoutSession(int $user_id):string|false {

		$cloud_session_uniq = self::_getCloudSessionUniqFromCookie();
		if ($cloud_session_uniq === false) {
			return false;
		}

		try {
			$user_active_session_row = Gateway_Db_CompanyData_SessionActiveList::getOne($cloud_session_uniq);
		} catch (\cs_RowIsEmpty) {

			Gateway_Bus_CompanyCache::clearSessionCacheByUserId($user_id);
			self::_clearCookie();
			return false;
		}

		// удаляем сессию из таблицы активных
		self::_deleteUserActiveSession($user_id, $cloud_session_uniq, $user_active_session_row);

		Gateway_Bus_CompanyCache::clearSessionCacheByUserId($user_id);
		self::_clearCookie();
		return $cloud_session_uniq;
	}

	/**
	 * удаляем активные сессии пользователя
	 *
	 * @param int                                 $user_id
	 * @param string                              $cloud_session_uniq
	 * @param Struct_Db_CompanyData_SessionActive $user_active_session_row
	 *
	 * @throws ReturnFatalException
	 * @throws \returnException
	 */
	protected static function _deleteUserActiveSession(int $user_id, string $cloud_session_uniq, Struct_Db_CompanyData_SessionActive $user_active_session_row):void {

		Gateway_Db_CompanyData_SessionHistoryList::beginTransaction();

		// удаляем сессию из таблицы активных
		try {

			Gateway_Db_CompanyData_SessionHistoryList::insert($cloud_session_uniq, $user_id,
				$user_active_session_row->user_company_session_token, self::_SESSION_STATUS_LOGGED_OUT, $user_active_session_row->created_at,
				$user_active_session_row->login_at, time(), getIp(), getUa(), $user_active_session_row->extra
			);
			Gateway_Db_CompanyData_SessionActiveList::delete($user_id, $cloud_session_uniq);
		} catch (\Exception $e) {

			Gateway_Db_CompanyData_SessionHistoryList::rollback();
			throw new ReturnFatalException($e);
		}

		Gateway_Db_CompanyData_SessionHistoryList::commitTransaction();
	}

	/**
	 * получить произвольный массив по ключу для текущей session_id + user_id
	 * возвращает false если запись в кэше не найдена
	 *
	 * @return array|false
	 *
	 * @throws \busException
	 * @throws \cs_SessionNotFound
	 */
	public static function getCache(string $key):bool|array {

		$val = ShardingGateway::cache()->get(self::_getKey($key));

		if ($val !== false) {
			$val = fromJson($val);
		}

		return $val;
	}

	/**
	 * записать произвольный массив по ключу для текущей session_id + user_id
	 *
	 * @throws \busException
	 * @throws \cs_SessionNotFound
	 */
	public static function setCache(string $key, array $data, int $expire = 300):void {

		\Compass\Company\ShardingGateway::cache()->set(self::_getKey($key), toJson($data), $expire);
	}

	/**
	 * очистить значение по ключу для текущей session_id + user_id
	 *
	 * @throws \busException
	 * @throws \cs_SessionNotFound
	 */
	public static function clearCache(string $key):void {

		\Compass\Company\ShardingGateway::cache()->delete(self::_getKey($key));
	}

	// функция для установки пользовательской сессии
	protected static function _setCookie(string $session_key):void {

		// если работаем из консоли
		if (isCLi()) {
			return;
		}

		$company_url = \CompassApp\System\Company::getCompanyDomain();

		$company_url_arr = parse_url("//" . $company_url);
		$company_path    = $company_url_arr["path"] ?? "/";
		$company_domain  = $company_url_arr["host"];

		// устанавливаем session_key для пользователя
		setcookie(
			self::_COMPANY_COOKIE_KEY,
			urlencode($session_key),
			time() + DAY1 * 360,
			$company_path, $company_domain,
			false,
			false
		);
	}

	// ------------------------------------------------------------
	// PROTECTED
	// ------------------------------------------------------------

	/**
	 * получаем session map из куки
	 *
	 * @return false|string
	 * @mixed
	 * @throws \cs_SessionNotFound
	 */
	protected static function _getCloudSessionUniqFromCookie():bool|string {

		// проверяем что сессии нет в куках
		if (!isset($_COOKIE[self::_COMPANY_COOKIE_KEY])) {
			return false;
		}

		try {
			return Type_Pack_CompanySession::getSessionUniq(Type_Pack_CompanySession::doDecrypt(urldecode($_COOKIE[self::_COMPANY_COOKIE_KEY])));
		} catch (\cs_DecryptHasFailed) {
			throw new \cs_SessionNotFound();
		}
	}

	// функция для очистки куки
	protected static function _clearCookie():void {

		unset($_COOKIE[self::_COMPANY_COOKIE_KEY]);

		if (!isCLi()) {

			$company_url = \CompassApp\System\Company::getCompanyDomain();

			$company_url_arr = parse_url("//" . $company_url);
			$company_path    = $company_url_arr["path"] ?? "/";
			$company_domain  = $company_url_arr["host"];

			setcookie(self::_COMPANY_COOKIE_KEY, "", -1, $company_path, $company_domain);
		}
	}

	/**
	 *
	 * @throws \busException
	 * @throws \cs_SessionNotFound
	 */
	protected static function _getKey(string $key):string {

		[$user_id, $cloud_session_uniq] = self::getSession();

		return $cloud_session_uniq . "_" . $user_id . "_" . $key;
	}
}
