<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс для работы с токенами аутентификации
 * @package Compass\Federation
 */
class Domain_Ldap_Entity_AuthToken {

	/** существующие статусы */
	public const STATUS_LDAP_AUTH_COMPLETE    = 1;
	public const STATUS_COMPASS_AUTH_COMPLETE = 2;
	public const STATUS_LDAP_AUTH_FAILED      = 8;

	/**
	 * сохраняем токен аутентфиикации
	 *
	 * @return Struct_Db_LdapData_LdapAuth
	 * @throws \queryException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function save(string $uid, string $username, string $dn, int $status, array $data):Struct_Db_LdapData_LdapAuth {

		$auth_token = new Struct_Db_LdapData_LdapAuth(
			ldap_auth_token: generateUUID(),
			status: $status,
			created_at: time(),
			updated_at: 0,
			uid: $uid,
			username: $username,
			dn: $dn,
			data: $data,
		);
		Gateway_Db_LdapData_LdapAuthList::insert($auth_token);

		return $auth_token;
	}

	/**
	 * получаем попытку
	 *
	 * @param string $ldap_auth_token
	 *
	 * @return Struct_Db_LdapData_LdapAuth
	 * @throws ParseFatalException
	 * @throws Domain_Ldap_Exception_Auth_TokenNotFound
	 */
	public static function get(string $ldap_auth_token):Struct_Db_LdapData_LdapAuth {

		try {
			return Gateway_Db_LdapData_LdapAuthList::getOne($ldap_auth_token);
		} catch (RowNotFoundException) {
			throw new Domain_Ldap_Exception_Auth_TokenNotFound();
		}
	}

	/**
	 * утверждаем, что статус попытки соответствует ожидаемой
	 *
	 * @throws Domain_Ldap_Exception_Auth_UnexpectedStatus
	 */
	public static function assertStatus(Struct_Db_LdapData_LdapAuth $auth, int $status):void {

		if ($auth->status !== $status) {
			throw new Domain_Ldap_Exception_Auth_UnexpectedStatus();
		}
	}

	/**
	 * при переходе попытки на статус @see self::STATUS_COMPASS_AUTH_COMPLETE
	 */
	public static function onCompassAuthComplete(Struct_Db_LdapData_LdapAuth $auth):void {

		Gateway_Db_LdapData_LdapAuthList::set($auth->ldap_auth_token, [
			"status"     => self::STATUS_COMPASS_AUTH_COMPLETE,
			"updated_at" => time(),
		]);
	}
}