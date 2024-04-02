<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс описывает работу с попыткой аутентификации
 * @package Compass\Federation
 */
class Domain_Sso_Entity_Auth {

	/** все возможные статусы */
	public const STATUS_IN_PROGRESS           = 1;
	public const STATUS_SSO_AUTH_COMPLETE     = 2;
	public const STATUS_COMPASS_AUTH_COMPLETE = 3;

	/** @var int сколько в секундах живет попытка */
	protected const _LIFE_TIME = 60 * 25;

	/**
	 * создаем попытку
	 *
	 * @param string $user_agent
	 * @param string $ip_address
	 *
	 * @return Struct_Db_SsoData_SsoAuth
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function create(string $user_agent, string $ip_address, string $redirect_url):Struct_Db_SsoData_SsoAuth {

		$sso_auth_draft = new Struct_Db_SsoData_SsoAuth(
			sso_auth_token: generateUUID(),
			signature: generateUUID(),
			status: self::STATUS_IN_PROGRESS,
			expires_at: time() + self::_LIFE_TIME,
			completed_at: 0,
			created_at: time(),
			updated_at: 0,
			link: "",
			ua_hash: sha1($user_agent),
			ip_address: $ip_address,
		);

		// формируем state
		$state = Domain_Sso_Entity_State::prepare($sso_auth_draft->sso_auth_token, $redirect_url);

		// формируем ссылку
		$sso_auth_draft->link = Domain_Sso_Entity_Oidc_Client::getAuthorizationURL($sso_auth_draft, $state);

		// пишем в базу
		Gateway_Db_SsoData_SsoAuthList::insert($sso_auth_draft);

		return $sso_auth_draft;
	}

	/**
	 * протухла ли попытка
	 *
	 * @param Struct_Db_SsoData_SsoAuth $auth
	 *
	 * @return bool
	 */
	public static function isExpired(Struct_Db_SsoData_SsoAuth $auth):bool {

		return $auth->expires_at < time();
	}

	/**
	 * утверждаем, что попытка не протухла
	 *
	 * @throws Domain_Sso_Exception_Auth_Expired
	 */
	public static function assertNotExpired(Struct_Db_SsoData_SsoAuth $auth):void {

		if (self::isExpired($auth)) {
			throw new Domain_Sso_Exception_Auth_Expired();
		}
	}

	/**
	 * завершена ли попытка (с ее помощью произвели аутентификацию в SSO)
	 *
	 * @param Struct_Db_SsoData_SsoAuth $auth
	 *
	 * @return bool
	 */
	public static function isSsoAuthComplete(Struct_Db_SsoData_SsoAuth $auth):bool {

		return $auth->status === self::STATUS_SSO_AUTH_COMPLETE;
	}

	/**
	 * утверждаем, что статус попытки соответствует ожидаемой
	 *
	 * @throws Domain_Sso_Exception_Auth_UnexpectedStatus
	 */
	public static function assertStatus(Struct_Db_SsoData_SsoAuth $auth, int $status):void {

		if ($auth->status !== $status) {
			throw new Domain_Sso_Exception_Auth_UnexpectedStatus();
		}
	}

	/**
	 * завершена ли попытка (с ее помощью произвели аутентификацию в Compass)
	 *
	 * @param Struct_Db_SsoData_SsoAuth $auth
	 *
	 * @return bool
	 */
	public static function isCompassAuthComplete(Struct_Db_SsoData_SsoAuth $auth):bool {

		return $auth->status === self::STATUS_COMPASS_AUTH_COMPLETE;
	}

	/**
	 * получаем попытку
	 *
	 * @param string $sso_auth_token
	 *
	 * @return Struct_Db_SsoData_SsoAuth
	 * @throws ParseFatalException
	 * @throws Domain_Sso_Exception_Auth_TokenNotFound
	 */
	public static function get(string $sso_auth_token):Struct_Db_SsoData_SsoAuth {

		try {
			return Gateway_Db_SsoData_SsoAuthList::getOne($sso_auth_token);
		} catch (RowNotFoundException) {
			throw new Domain_Sso_Exception_Auth_TokenNotFound();
		}
	}

	/**
	 * сверяем подпись попытки
	 *
	 * @throws Domain_Sso_Exception_Auth_SignatureMismatch
	 */
	public static function assertSignature(Struct_Db_SsoData_SsoAuth $auth, string $signature):void {

		if ($auth->signature !== $signature) {
			throw new Domain_Sso_Exception_Auth_SignatureMismatch();
		}
	}

	/**
	 * при переходе попытки на статус @see self::STATUS_SSO_AUTH_COMPLETE
	 */
	public static function onSsoAuthComplete(Struct_Db_SsoData_SsoAuth $sso_auth):void {

		Gateway_Db_SsoData_SsoAuthList::set($sso_auth->sso_auth_token, [
			"status"     => self::STATUS_SSO_AUTH_COMPLETE,
			"updated_at" => time(),
		]);
	}

	/**
	 * при переходе попытки на статус @see self::STATUS_COMPASS_AUTH_COMPLETE
	 */
	public static function onCompassAuthComplete(Struct_Db_SsoData_SsoAuth $sso_auth):void {

		Gateway_Db_SsoData_SsoAuthList::set($sso_auth->sso_auth_token, [
			"status"     => self::STATUS_COMPASS_AUTH_COMPLETE,
			"updated_at" => time(),
		]);
	}
}