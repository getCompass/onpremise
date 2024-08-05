<?php

namespace Compass\Pivot;

/**
 * абстрактный класс, который наследует каждый класс-хендлер метода аутентификации
 * @package Compass\Pivot
 */
abstract class Domain_User_Entity_AuthStory_MethodHandler_Default {

	/** через сколько истекает попытка аутентификации */
	public const STORY_LIFE_TIME = 60 * 20;

	protected Struct_Db_PivotAuth_AuthDefault $_auth_entity;

	/**
	 * получить map идентификатор аутентификации
	 *
	 * @return string
	 */
	abstract public function getAuthMap():string;

	/**
	 * получаем параметр аутентификации с помощью которого была начата попытка
	 *
	 * для аутентификации через телефон – это номер
	 * для аутентификации через почту – это адрес почты
	 *
	 * @return string
	 */
	abstract public function getAuthParameter():string;

	/**
	 * конвертируем объект способа аутентификации в ассоц. массив
	 *
	 * @return array
	 */
	abstract public function authEntityToArray():array;

	/**
	 * Обрабатываем успешное завершение аутентификации
	 *
	 * @return $this
	 */
	abstract public function handleSuccess(int $user_id, array $additional_update_field_list):static;

	/**
	 * инициализируем нужный класс хендлера в зависимости от типа аутентификации
	 *
	 * @return static
	 */
	public static function init(Struct_Db_PivotAuth_Auth $auth, array $auth_method_data):static {

		return match ($auth->type) {
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_REGISTER_BY_PHONE_NUMBER,
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_LOGIN_BY_PHONE_NUMBER  => new Domain_User_Entity_AuthStory_MethodHandler_PhoneNumber(new Struct_Db_PivotAuth_AuthPhone(...array_values($auth_method_data))),
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_REGISTER_BY_MAIL,
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_LOGIN_BY_MAIL,
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_RESET_PASSWORD_BY_MAIL => new Domain_User_Entity_AuthStory_MethodHandler_Mail(new Struct_Db_PivotAuth_AuthMail(...array_values($auth_method_data))),
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_AUTH_BY_SSO            => new Domain_User_Entity_AuthStory_MethodHandler_Sso(new Struct_Db_PivotAuth_AuthSso(...array_values($auth_method_data))),
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_AUTH_BY_LDAP           => new Domain_User_Entity_AuthStory_MethodHandler_Ldap(new Struct_Db_PivotAuth_AuthLdap(...array_values($auth_method_data))),
		};
	}

	/**
	 * инициализируем нужный класс хендлера в зависимости от типа аутентификации с запросом в базу за сущностью способа аутентификации
	 *
	 * @return static
	 * @throws \cs_RowIsEmpty
	 */
	public static function get(Struct_Db_PivotAuth_Auth $auth, string $auth_map):static {

		$auth_method_entity = match ($auth->type) {
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_REGISTER_BY_PHONE_NUMBER,
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_LOGIN_BY_PHONE_NUMBER  => Gateway_Db_PivotAuth_AuthPhoneList::getOne($auth_map),
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_REGISTER_BY_MAIL,
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_LOGIN_BY_MAIL,
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_RESET_PASSWORD_BY_MAIL => Gateway_Db_PivotAuth_AuthMailList::getOne($auth_map),
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_AUTH_BY_SSO            => Gateway_Db_PivotAuth_AuthSsoList::getOne($auth_map),
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_AUTH_BY_LDAP           => Gateway_Db_PivotAuth_AuthLdapList::getOne($auth_map),
		};

		return match ($auth->type) {
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_REGISTER_BY_PHONE_NUMBER,
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_LOGIN_BY_PHONE_NUMBER  => new Domain_User_Entity_AuthStory_MethodHandler_PhoneNumber($auth_method_entity),
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_REGISTER_BY_MAIL,
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_LOGIN_BY_MAIL,
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_RESET_PASSWORD_BY_MAIL => new Domain_User_Entity_AuthStory_MethodHandler_Mail($auth_method_entity),
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_AUTH_BY_SSO            => new Domain_User_Entity_AuthStory_MethodHandler_Sso($auth_method_entity),
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_AUTH_BY_LDAP           => new Domain_User_Entity_AuthStory_MethodHandler_Ldap($auth_method_entity),
		};
	}
}