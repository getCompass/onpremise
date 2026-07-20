<?php

namespace Compass\Federation;

use BaseFrame\Exception\Gateway\DBShardingNotFoundException;
use BaseFrame\Exception\Gateway\QueryFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс для работы с привязкой TOTP к пользователю
 * @package Compass\Federation
 */
class Domain_Ldap_Entity_Totp_UserRel
{
	public const TOTP_SECRET_CRYPT_KEY = "totp_secret_crypt_key";

	public string $uid;

	public string $crypted_totp_secret;

	public int $created_at;

	public int $updated_at;

	protected function __construct()
	{
	}

	/**
	 * Создать новую привязку TOTP
	 *
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function create(string $uid, string $crypted_totp_secret): void
	{

		$db_totp_user_rel = new Struct_Db_LdapData_TotpUserRel(
			$uid,
			$crypted_totp_secret,
			time(),
			0,
		);

		Gateway_Db_LdapData_TotpUserRel::insert($db_totp_user_rel);
	}

	/**
	 * Получить привязку TOTP
	 *
	 * @throws DBShardingNotFoundException
	 * @throws Domain_Ldap_Exception_Totp_NotBound
	 * @throws QueryFatalException
	 */
	public static function get(string $uid): self
	{

		try {
			$db_totp_user_rel = Gateway_Db_LdapData_TotpUserRel::getOne($uid);
		} catch (RowNotFoundException) {
			throw new Domain_Ldap_Exception_Totp_NotBound();
		}

		$totp_user_rel                      = new self();
		$totp_user_rel->uid                 = $db_totp_user_rel->uid;
		$totp_user_rel->crypted_totp_secret = $db_totp_user_rel->crypted_totp_secret;
		$totp_user_rel->created_at          = $db_totp_user_rel->created_at;
		$totp_user_rel->updated_at          = $db_totp_user_rel->updated_at;

		return $totp_user_rel;
	}

	/**
	 * Удалить привязку TOTP
	 *
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function delete(string $uid): void
	{

		Gateway_Db_LdapData_TotpUserRel::delete($uid);
	}
}
