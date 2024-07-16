<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс для работы с пользовательской сессией
 */
class Type_Session_Main extends \CompassApp\Domain\Session\Main {

	protected const _TOKEN_HEADER_UNIQUE = "_company_authorization_token";

	protected const _SESSION_STATUS_LOGGED_IN  = 2;
	protected const _SESSION_STATUS_LOGGED_OUT = 3;

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
		self::_setRequestAuthData($session_key);

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
	 * Инвалидируем сессию
	 * @throws \cs_SessionNotFound
	 */
	public static function doLogoutSession(int $user_id):string|false {

		$cloud_session_uniq = static::_getCloudSessionUniq();
		if ($cloud_session_uniq === false) {
			return false;
		}

		try {
			$user_active_session_row = Gateway_Db_CompanyData_SessionActiveList::getOne($cloud_session_uniq);
		} catch (\cs_RowIsEmpty) {

			Gateway_Bus_CompanyCache::clearSessionCacheByUserId($user_id);
			static::_clearRequestAuthData();
			return false;
		}

		// удаляем сессию из таблицы активных
		static::_deleteUserActiveSession($user_id, $cloud_session_uniq, $user_active_session_row);

		Gateway_Bus_CompanyCache::clearSessionCacheByUserId($user_id);
		static::_clearRequestAuthData();

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
	 * Устанавливает клиенту данные авторизации.
	 */
	protected static function _setRequestAuthData(string $session_key):void {

		static::setHeaderAction($session_key);
		static::_setCookie($session_key);
	}

	/**
	 * Устанавливает данные, которые клиент клиент будет использовать для авторизации в дальнейшем.
	 *
	 * <b>!!! Функция имеет public уровень только для миграции куки -> заголовок, в остальных
	 * случая публичное использование функции запрещено !!!<b>
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

		$company_url = \CompassApp\System\Company::getCompanyDomain();

		$company_url_arr = parse_url("//" . $company_url);
		$company_path    = $company_url_arr["path"] ?? "/";
		$company_domain  = $company_url_arr["host"];

		// устанавливаем session_key для пользователя
		setcookie(
			static::_makeCookieKey(),
			urlencode($session_key),
			time() + DAY1 * 360,
			$company_path, $company_domain,
			false,
			false
		);
	}

	/**
	 * Сбрасывает данные авторизации запроса.
	 */
	protected static function _clearRequestAuthData():void {

		static::_clearHeader();
		static::_clearCookie();
	}

	/**
	 * Готовит данные для action сброса заголовка авторизации.
	 */
	protected static function _clearHeader():void {

		// инвалидируем заголовок
		\BaseFrame\Http\Header\Authorization::invalidate();

		// устанавливаем данные для authorization action
		\BaseFrame\Http\Authorization\Data::inst()->drop(static::_makeTokeUniq());
	}

	/**
	 * Очищает cookie авторизации клиенту.
	 */
	protected static function _clearCookie():void {

		// сбрасываем куку авторизации
		unset($_COOKIE[static::_makeCookieKey()]);

		if (!isCLi()) {

			$company_url = \CompassApp\System\Company::getCompanyDomain();

			$company_url_arr = parse_url("//" . $company_url);
			$company_path    = $company_url_arr["path"] ?? "/";
			$company_domain  = $company_url_arr["host"];

			setcookie(static::_makeCookieKey(), "", -1, $company_path, $company_domain);
		}
	}

	/**
	 * Пытается получить сессию из токена заголовка авторизации.
	 */
	public static function tryGetCloudSessionKeyFromAuthorizationHeader():array {

		return parent::_tryGetCloudSessionKeyFromAuthorizationHeader();
	}

	/**
	 * Пытается получить токен из cookie.
	 */
	public static function tryGetCloudSessionKeyFromCookie():string|false {

		return parent::_tryGetCloudSessionKeyFromCookie();
	}

	/**
	 * Формирует объект с данными авторизации клиента.
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["unique" => "string", "token" => "string"])]
	public static function makeAuthenticationItem(string $session_key):array {

		return [
			"unique" => static::_makeTokeUniq(),
			"token"  => sprintf("%s %s", static::_HEADER_AUTH_TYPE, base64_encode($session_key))
		];
	}

	/**
	 * Генерирует уникальный идентификатор токена для клиента.
	 */
	protected static function _makeTokeUniq():string {

		return \CompassApp\System\Company::getCompanyId() . static::_TOKEN_HEADER_UNIQUE;
	}
}
