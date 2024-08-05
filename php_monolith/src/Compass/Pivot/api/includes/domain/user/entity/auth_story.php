<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * Класс для получения данных об истории аутентификаций
 */
class Domain_User_Entity_AuthStory {

	/** возможные типы аутентификации: */
	public const AUTH_STORY_TYPE_REGISTER_BY_PHONE_NUMBER = 1; // тип регистрации через номер телефона
	public const AUTH_STORY_TYPE_LOGIN_BY_PHONE_NUMBER    = 2; // тип авторизации через номер телефона
	public const AUTH_STORY_TYPE_REGISTER_BY_MAIL         = 3; // тип регистрации через почту
	public const AUTH_STORY_TYPE_LOGIN_BY_MAIL            = 4; // тип авторизации через почту
	public const AUTH_STORY_TYPE_RESET_PASSWORD_BY_MAIL   = 5; // тип для сброса пароля через почту из под неавторизованного пользователя
	public const AUTH_STORY_TYPE_AUTH_BY_SSO              = 6; // тип аутентификации через SSO
	public const AUTH_STORY_TYPE_CHANGE_MAIL              = 7; // тип для сброса пароля через почту из под неавторизованного пользователя
	public const AUTH_STORY_TYPE_AUTH_BY_LDAP             = 8; // тип аутентификации через LDAP

	/** возможные статусы аутентификации для истории: */
	public const HISTORY_AUTH_STATUS_SUCCESS = 1; // успешная аутентификация

	/**
	 * Domain_User_Entity_AuthStory constructor.
	 */
	public function __construct(
		protected Struct_Db_PivotAuth_Auth                           $_auth,
		protected Domain_User_Entity_AuthStory_MethodHandler_Default $_auth_method_entity,
	) {

	}

	/**
	 * получить из кэша
	 *
	 * @return static
	 *
	 * @throws cs_CacheIsEmpty
	 */
	public static function getFromSessionCache(string $auth_parameter):self {

		$cache = Type_Session_Main::getCache(self::class);

		if (!isset($cache[$auth_parameter])) {
			throw new cs_CacheIsEmpty();
		}

		// получаем историю по параметру аутентификации
		$cached_auth_story = $cache[$auth_parameter];

		$auth = new Struct_Db_PivotAuth_Auth(...array_values($cached_auth_story["auth"]));

		return new static(
			$auth,
			Domain_User_Entity_AuthStory_MethodHandler_Default::init($auth, $cached_auth_story["auth_method_data"])
		);
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

			$auth        = Gateway_Db_PivotAuth_AuthList::getOne($auth_map);
			$auth_method = Domain_User_Entity_AuthStory_MethodHandler_Default::get($auth, $auth_map);
		} catch (\cs_RowIsEmpty|RowNotFoundException) {
			throw new cs_WrongAuthKey();
		}

		return new static(
			$auth,
			$auth_method
		);
	}

	/**
	 * получаем класс-хендлер аутентификации по номеру
	 *
	 * @return Domain_User_Entity_AuthStory_MethodHandler_PhoneNumber|Domain_User_Entity_AuthStory_MethodHandler_Default
	 * @throws ParseFatalException
	 */
	public function getAuthPhoneHandler():Domain_User_Entity_AuthStory_MethodHandler_PhoneNumber|Domain_User_Entity_AuthStory_MethodHandler_Default {

		if (false === ($this->_auth_method_entity instanceof Domain_User_Entity_AuthStory_MethodHandler_PhoneNumber)) {
			throw new ParseFatalException("unexpected behaviour");
		}

		return $this->_auth_method_entity;
	}

	/**
	 * получаем класс-хендлер аутентификации по почте
	 *
	 * @return Domain_User_Entity_AuthStory_MethodHandler_Mail|Domain_User_Entity_AuthStory_MethodHandler_Default
	 * @throws ParseFatalException
	 */
	public function getAuthMailHandler():Domain_User_Entity_AuthStory_MethodHandler_Mail|Domain_User_Entity_AuthStory_MethodHandler_Default {

		if (false === ($this->_auth_method_entity instanceof Domain_User_Entity_AuthStory_MethodHandler_Mail)) {
			throw new ParseFatalException("unexpected behaviour");
		}

		return $this->_auth_method_entity;
	}

	/**
	 * получаем класс-хендлер аутентификации по SSO
	 *
	 * @return Domain_User_Entity_AuthStory_MethodHandler_Sso|Domain_User_Entity_AuthStory_MethodHandler_Default
	 * @throws ParseFatalException
	 */
	public function getAuthSsoHandler():Domain_User_Entity_AuthStory_MethodHandler_Sso|Domain_User_Entity_AuthStory_MethodHandler_Default {

		if (false === ($this->_auth_method_entity instanceof Domain_User_Entity_AuthStory_MethodHandler_Sso)) {
			throw new ParseFatalException("unexpected behaviour");
		}

		return $this->_auth_method_entity;
	}

	/**
	 * получаем класс-хендлер аутентификации по LDAP
	 *
	 * @return Domain_User_Entity_AuthStory_MethodHandler_Ldap|Domain_User_Entity_AuthStory_MethodHandler_Default
	 * @throws ParseFatalException
	 */
	public function getAuthLdapHandler():Domain_User_Entity_AuthStory_MethodHandler_Ldap|Domain_User_Entity_AuthStory_MethodHandler_Default {

		if (false === ($this->_auth_method_entity instanceof Domain_User_Entity_AuthStory_MethodHandler_Ldap)) {
			throw new ParseFatalException("unexpected behaviour");
		}

		return $this->_auth_method_entity;
	}

	/**
	 * чистим кеш по номеру телефона
	 *
	 * @return $this
	 */
	public function clearAuthCache():self {

		$cache = Type_Session_Main::getCache(self::class);

		if (!isset($cache[$this->_auth_method_entity->getAuthParameter()])) {
			return $this;
		}

		unset($cache[$this->_auth_method_entity->getAuthParameter()]);
		Type_Session_Main::setCache(self::class, $cache, $this->_auth_method_entity::STORY_LIFE_TIME);

		return $this;
	}

	/**
	 * сохранить в кэше сессии
	 *
	 * @return $this
	 */
	public function storeInSessionCache():self {

		$cache = Type_Session_Main::getCache(self::class);

		$cache[$this->_auth_method_entity->getAuthParameter()] = [
			"auth"             => $this->_auth,
			"auth_method_data" => $this->_auth_method_entity->authEntityToArray(),
		];
		Type_Session_Main::setCache(self::class, $cache, $this->_auth_method_entity::STORY_LIFE_TIME);

		return $this;
	}

	/**
	 * создаем аутентификацию
	 *
	 * @return static
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \queryException
	 * @throws cs_IncorrectSaltVersion
	 */
	public static function create(int $user_id, int $type, int $expires_at, array $auth_method_data):static {

		// данные для новой записи
		$auth_uniq = generateUUID();
		$time      = time();

		// данные для шардинга
		$shard_id = Type_Pack_Auth::getShardIdByTime($time);
		$table_id = Type_Pack_Auth::getTableIdByTime($time);
		$auth_map = Type_Pack_Auth::doPack($auth_uniq, $shard_id, $table_id, $time);

		Gateway_Db_PivotAuth_Main::beginTransaction($shard_id);

		// вставляем запись об аутентификации пользователя
		$auth = new Struct_Db_PivotAuth_Auth(
			$auth_uniq, $user_id, 0, $type, $time, 0, $expires_at, Type_Hash_UserAgent::makeHash(getUa()), getIp()
		);
		Gateway_Db_PivotAuth_AuthList::insert($auth);

		$auth_method_data["auth_map"] = $auth_map;
		$auth_method                  = Domain_User_Entity_AuthStory_MethodHandler_Default::init($auth, $auth_method_data);
		$auth_method->create();

		Gateway_Db_PivotAuth_Main::commitTransaction($shard_id);

		return new static($auth, $auth_method);
	}

	/**
	 * получаем продолжительность жизни попытки
	 *
	 * @return int
	 */
	public function getLifeTime():int {

		return $this->_auth_method_entity::STORY_LIFE_TIME;
	}

	/**
	 * проверяем, что телефоны совпадают
	 *
	 * @return $this
	 *
	 * @throws Domain_User_Exception_AuthStory_AuthParameterNotEqual
	 */
	public function assertAuthParameter(string $auth_parameter):self {

		if ($this->_auth_method_entity->getAuthParameter() !== $auth_parameter) {
			throw new Domain_User_Exception_AuthStory_AuthParameterNotEqual("auth parameter not equal");
		}

		return $this;
	}

	/**
	 * проверяем, истекла ли аутентификация
	 *
	 * @return $this
	 *
	 * @throws cs_AuthIsExpired
	 */
	public function assertNotExpired():self {

		if ($this->_auth->expires_at < time()) {
			throw new cs_AuthIsExpired();
		}

		return $this;
	}

	/**
	 * получить данные об аутентификации для пользователя
	 *
	 */
	public function getAuthInfo():Struct_User_Auth_Info {

		return new Struct_User_Auth_Info(
			$this->getAuthMap(),
			$this->_auth,
			$this->_auth_method_entity
		);
	}

	/**
	 * получаем map-идентификатор аутентификации
	 *
	 * @return string
	 */
	public function getAuthMap():string {

		return $this->_auth_method_entity->getAuthMap();
	}

	/**
	 * получаем время, когда протухнет попытка
	 */
	public function getExpiresAt():string {

		return $this->_auth->expires_at;
	}

	/**
	 * проверяем, что аутентификация еще не завершена
	 *
	 * @return $this
	 *
	 * @throws cs_AuthAlreadyFinished
	 */
	public function assertNotFinishedYet():self {

		if ($this->_auth->is_success) {
			throw new cs_AuthAlreadyFinished();
		}

		return $this;
	}

	/**
	 * проверяем, что переданный тип совпадает типу аутентификации
	 *
	 * @return $this
	 * @throws Domain_User_Exception_AuthStory_TypeMismatch
	 */
	public function assertType(array $expected_type_list):self {

		if (!in_array($this->_auth->type, $expected_type_list)) {
			throw new Domain_User_Exception_AuthStory_TypeMismatch();
		}

		return $this;
	}

	/**
	 * получаем user_id пользователя, который логинится
	 */
	public function getUserId():int {

		return $this->_auth->user_id;
	}

	/**
	 * получаем type аутентификации
	 */
	public function getType():int {

		return $this->_auth->type;
	}

	/**
	 * завершена ли успешно аутентификация
	 *
	 * @return bool
	 */
	public function isSuccess():bool {

		return (bool) $this->_auth->is_success;
	}

	/**
	 * проверяем, надо ли создавать пользователя
	 *
	 */
	public function isNeedToCreateUser():bool {

		// если это аутентификация через SSO
		if ($this->_auth->type === self::AUTH_STORY_TYPE_AUTH_BY_SSO) {
			return $this->_auth->user_id === 0;
		}

		// если это аутентификация через LDAP
		if ($this->_auth->type === self::AUTH_STORY_TYPE_AUTH_BY_LDAP) {
			return $this->_auth->user_id === 0;
		}

		return in_array($this->_auth->type, [
			self::AUTH_STORY_TYPE_REGISTER_BY_PHONE_NUMBER,
			self::AUTH_STORY_TYPE_REGISTER_BY_MAIL,
		]);
	}

	/**
	 * Обновляем аутентификацию при ее успешном завершении
	 *
	 * @return $this
	 * @throws \parseException
	 */
	public function handleSuccess(int $user_id, array $additional_update_auth_method_entity_field_list = []):self {

		$this->_auth->user_id    = $user_id;
		$this->_auth->is_success = 1;
		$this->_auth->updated_at = time();
		Gateway_Db_PivotAuth_AuthList::set($this->getAuthInfo()->auth_map, [
			"user_id"    => $this->_auth->user_id,
			"is_success" => $this->_auth->is_success,
			"updated_at" => $this->_auth->updated_at,
		]);

		$this->_auth_method_entity->handleSuccess($user_id, $additional_update_auth_method_entity_field_list);

		return $this;
	}

	/**
	 * это аутентификация по номеру телефона
	 *
	 * @return bool
	 */
	public static function isPhoneNumberAuth(int $auth_type):bool {

		return in_array($auth_type, [
			self::AUTH_STORY_TYPE_REGISTER_BY_PHONE_NUMBER,
			self::AUTH_STORY_TYPE_LOGIN_BY_PHONE_NUMBER,
		]);
	}

	/**
	 * это аутентификация по почте
	 *
	 * @return bool
	 */
	public static function isMailAuth(int $auth_type):bool {

		return in_array($auth_type, [
			self::AUTH_STORY_TYPE_REGISTER_BY_MAIL,
			self::AUTH_STORY_TYPE_LOGIN_BY_MAIL,
		]);
	}

	/**
	 * это аутентификация по SSO
	 *
	 * @return bool
	 */
	public static function isSsoAuth(int $auth_type):bool {

		return $auth_type == self::AUTH_STORY_TYPE_AUTH_BY_SSO;
	}

	/**
	 * это сброс пароля по почте
	 *
	 * @return bool
	 */
	public static function isMailResetPassword(int $auth_type):bool {

		return $auth_type === self::AUTH_STORY_TYPE_RESET_PASSWORD_BY_MAIL;
	}
}