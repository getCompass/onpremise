<?php

namespace Compass\Pivot;

/**
 * Класс для получения данных об истории аутентификаций
 *
 * Class Domain_User_Entity_AuthStory
 */
class Domain_User_Entity_AuthStory {

	protected Struct_Db_PivotAuth_Auth      $auth;
	protected Struct_Db_PivotAuth_AuthPhone $auth_phone;

	public const NEXT_ATTEMPT_AFTER = 60;      // через сколько доступна пересылка смски
	public const EXPIRE_AT          = 60 * 20; // через сколько истекает попытка входа/регистрации

	public const AUTH_STORY_TYPE_REGISTER = 1; // тип регистрации
	public const AUTH_STORY_TYPE_LOGIN    = 2; // тип аутентификации

	public const RESEND_COUNT_LIMIT           = 3; // лимит переотправки смс
	public const ERROR_COUNT_LIMIT            = 3; // лимит на кол-во ошибок
	public const ONPREMISE_RESEND_COUNT_LIMIT = 7; // лимит переотправки смс для онпремайза
	public const ONPREMISE_ERROR_COUNT_LIMIT  = 7; // лимит на кол-во ошибок для онпремайза

	public const AUTH_STATUS_SUCCESS = 1; // успешная аутентификация

	/**
	 * Domain_User_Entity_AuthStory constructor.
	 *
	 */
	public function __construct(Struct_User_Auth_Story $story) {

		$this->auth       = $story->auth;
		$this->auth_phone = $story->auth_phone;
	}

	/**
	 * получить из кэша
	 *
	 * @return static
	 *
	 * @throws cs_CacheIsEmpty
	 */
	public static function getFromSessionCache(string $phone_number):self {

		$cached_story = Type_Session_Main::getCache(self::class);

		if (!isset($cached_story[$phone_number])) {
			throw new cs_CacheIsEmpty();
		}

		// получаем историю по номеру телефона
		$cached_story_by_number = $cached_story[$phone_number];

		$story = new Struct_User_Auth_Story(
			new Struct_Db_PivotAuth_Auth(...array_values($cached_story_by_number["auth"])),
			new Struct_Db_PivotAuth_AuthPhone(...array_values($cached_story_by_number["auth_phone"]))
		);

		return new static($story);
	}

	/**
	 * получить по ключу аутентификации
	 *
	 * @return static
	 *
	 * @throws cs_WrongAuthKey
	 */
	public static function getByMap(string $auth_map):self {

		try {

			$auth       = Gateway_Db_PivotAuth_AuthList::getOne($auth_map);
			$auth_phone = Gateway_Db_PivotAuth_AuthPhoneList::getOne($auth_map);
		} catch (\cs_RowIsEmpty) {
			throw new cs_WrongAuthKey();
		}

		$story = new Struct_User_Auth_Story($auth, $auth_phone);

		return new static($story);
	}

	/**
	 * чистим кеш по номеру телефона
	 *
	 * @return $this
	 */
	public function clearAuthCacheByPhoneNumber():self {

		$cache = Type_Session_Main::getCache(self::class);

		if (!isset($cache[$this->auth_phone->phone_number])) {
			return $this;
		}

		unset($cache[$this->auth_phone->phone_number]);
		Type_Session_Main::setCache(self::class, $cache, self::EXPIRE_AT);

		return $this;
	}

	/**
	 * очистить кэш, лежащий по ключу сессии
	 */
	public static function clearAuthCache():void {

		Type_Session_Main::clearCache(self::class);
	}

	/**
	 * сохранить в кэше сессии
	 *
	 * @return $this
	 */
	public function storeInSessionCache():self {

		$cache = Type_Session_Main::getCache(self::class);

		$cache[$this->auth_phone->phone_number] = [
			"auth"       => $this->auth,
			"auth_phone" => $this->auth_phone,
		];
		Type_Session_Main::setCache(self::class, $cache, self::EXPIRE_AT);

		return $this;
	}

	/**
	 * получить структурные данные об аутентификации
	 *
	 */
	public function getStoryData():Struct_User_Auth_Story {

		return new Struct_User_Auth_Story(
			$this->auth,
			$this->auth_phone
		);
	}

	/**
	 * проверяем, истекла ли аутентификация
	 *
	 * @return $this
	 *
	 * @throws cs_AuthIsExpired
	 */
	public function assertNotExpired():self {

		if ($this->auth->expires_at < time()) {
			throw new cs_AuthIsExpired();
		}

		return $this;
	}

	/**
	 * проверяем, что телефоны совпадают
	 *
	 * @return $this
	 *
	 * @throws cs_PhoneNumberIsNotEqual
	 */
	public function assertPhoneNumber(string $phone_number):self {

		if ($this->auth_phone->phone_number !== $phone_number) {
			throw new cs_PhoneNumberIsNotEqual();
		}

		return $this;
	}

	/**
	 * проверяем, что аутентификация еще не завершена
	 *
	 * @return $this
	 *
	 * @throws cs_AuthAlreadyFinished
	 */
	public function assertNotFinishedYet():self {

		if ($this->auth->is_success) {
			throw new cs_AuthAlreadyFinished();
		}

		return $this;
	}

	/**
	 * проверяем, что лимит ошибок не превышен
	 *
	 * @return $this
	 *
	 * @throws cs_PhoneNumberIsBlocked
	 */
	public function assertErrorCountLimitNotExceeded(int $error_count_limit = self::ERROR_COUNT_LIMIT):self {

		if ($this->auth_phone->error_count >= $error_count_limit) {
			throw new cs_PhoneNumberIsBlocked($this->auth->expires_at);
		}

		return $this;
	}

	/**
	 * проверяем, что код совпадает
	 *
	 * @return $this
	 *
	 * @throws cs_IncorrectSaltVersion
	 * @throws cs_InvalidHashStruct
	 * @throws cs_WrongSmsCode
	 */
	public function assertEqualCode(string $code, int $error_count_limit = self::ERROR_COUNT_LIMIT):self {

		if (!Type_Hash_Code::compareHash($this->auth_phone->sms_code_hash, $code)) {

			throw new cs_WrongSmsCode(
				$this->getAvailableAttempts($error_count_limit) - 1,
				$this->auth->expires_at
			);
		}

		return $this;
	}

	/**
	 * проверяем, что число переотправок не превышено
	 *
	 * @return $this
	 *
	 * @throws cs_ResendSmsCountLimitExceeded
	 */
	public function assertResendCountLimitNotExceeded(int $resend_count_limit = self::RESEND_COUNT_LIMIT):self {

		if ($this->auth_phone->resend_count >= $resend_count_limit) {
			throw new cs_ResendSmsCountLimitExceeded();
		}

		return $this;
	}

	/**
	 * проверяем, что переотправка доступна
	 *
	 * @return $this
	 *
	 * @throws cs_ResendWillBeAvailableLater
	 */
	public function assertResendIsAvailable():self {

		if ($this->auth_phone->next_resend_at > time()) {
			throw new cs_ResendWillBeAvailableLater($this->auth_phone->next_resend_at);
		}

		return $this;
	}

	/**
	 * получить данные об аутентификации для пользователя
	 *
	 */
	public function getAuthInfo(int $error_count_limit = self::ERROR_COUNT_LIMIT, int $resend_count_limit = self::RESEND_COUNT_LIMIT):Struct_User_Auth_Info {

		$available_attempts = $this->getAvailableAttempts($error_count_limit);
		$next_resend        = $this->getNextResend($resend_count_limit);

		return new Struct_User_Auth_Info(
			$this->auth_phone->auth_map,
			$next_resend,
			$available_attempts,
			$this->auth->expires_at,
			(new \BaseFrame\System\PhoneNumber($this->auth_phone->phone_number))->obfuscate(),
			$this->auth->type
		);
	}

	/**
	 * получаем user_id пользователя, который логинится
	 *
	 */
	public function getUserId():int {

		return $this->auth->user_id;
	}

	/**
	 * получаем номер телефона пользователя, который логинится
	 *
	 */
	public function getPhoneNumber():string {

		return $this->auth_phone->phone_number;
	}

	/**
	 * проверяем, надо ли создавать пользователя
	 *
	 */
	public function isNeedToCreateUser():bool {

		return $this->auth->type === self::AUTH_STORY_TYPE_REGISTER;
	}

	/**
	 * получаем доступное кол-во попыток
	 *
	 */
	public function getAvailableAttempts(int $error_count_limit = self::ERROR_COUNT_LIMIT):int {

		return $error_count_limit - $this->auth_phone->error_count;
	}

	/**
	 * получаем время, когда можно сделать следующую переотправку
	 *
	 */
	public function getNextResend(int $resend_count_limit = self::RESEND_COUNT_LIMIT):int {

		if ($this->auth_phone->resend_count == $resend_count_limit) {
			return 0;
		}
		return $this->auth_phone->next_resend_at;
	}

	/**
	 * получаем sms_id
	 */
	public function getSmsId():string {

		return $this->auth_phone->sms_id;
	}

	/**
	 * получаем время, когда протухнет попытка
	 */
	public function getExpiresAt():string {

		return $this->auth->expires_at;
	}
}