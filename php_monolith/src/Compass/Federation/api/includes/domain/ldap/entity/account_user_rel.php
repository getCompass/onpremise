<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\RowDuplicationException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс для работы со связью «LDAP аккаунт» – «Пользователь Compass»
 * @package Compass\Federation
 */
class Domain_Ldap_Entity_AccountUserRel {

	/** существующие статусы для связи */
	public const STATUS_ACTIVE   = 1;
	public const STATUS_DISABLED = 8;

	/**
	 * получаем связь для переданного UID
	 *
	 * @param string $uid
	 *
	 * @return Struct_Db_LdapData_LdapAccountUserRel
	 * @throws ParseFatalException
	 * @throws Domain_Ldap_Exception_UserRelationship_NotFound
	 */
	public static function get(string $uid):Struct_Db_LdapData_LdapAccountUserRel {

		try {
			return Gateway_Db_LdapData_LdapAccountUserRel::getOne($uid);
		} catch (RowNotFoundException) {
			throw new Domain_Ldap_Exception_UserRelationship_NotFound();
		}
	}

	/**
	 * создаем связь «LDAP аккаунт» – «Пользователь Compass»
	 *
	 * @throws Domain_Sso_Exception_UserRelationship_AlreadyExists
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function create(string $uid, int $user_id, string $username, string $dn):void {

		$ldap_account_user_rel = new Struct_Db_LdapData_LdapAccountUserRel(
			uid: $uid,
			user_id: $user_id,
			status: self::STATUS_ACTIVE,
			created_at: time(),
			updated_at: 0,
			username: $username,
			dn: $dn,
		);

		try {
			Gateway_Db_LdapData_LdapAccountUserRel::insert($ldap_account_user_rel);
		} catch (RowDuplicationException) {
			throw new Domain_Ldap_Exception_UserRelationship_AlreadyExists();
		}
	}

	/**
	 * получаем связь по user_id
	 *
	 * @return Struct_Db_LdapData_LdapAccountUserRel
	 * @throws Domain_Ldap_Exception_UserRelationship_NotFound
	 * @throws ParseFatalException
	 */
	public static function getByUserID(int $user_id):Struct_Db_LdapData_LdapAccountUserRel {

		try {
			return Gateway_Db_LdapData_LdapAccountUserRel::getOneByUserID($user_id);
		} catch (RowNotFoundException) {
			throw new Domain_Ldap_Exception_UserRelationship_NotFound();
		}
	}

	/**
	 * получаем все записи из таблицы
	 *
	 * @return Struct_Db_LdapData_LdapAccountUserRel[]
	 * @throws ParseFatalException
	 */
	public static function getAll():array {

		return Gateway_Db_LdapData_LdapAccountUserRel::getAll();
	}

	/**
	 * устанавливаем новый статус для связи
	 *
	 * @param string $uid
	 * @param int    $status
	 *
	 * @throws ParseFatalException
	 */
	public static function setStatus(string $uid, int $status):void {

		Gateway_Db_LdapData_LdapAccountUserRel::set($uid, [
			"status"     => $status,
			"updated_at" => time(),
		]);
	}

	/**
	 * удаляем связь
	 *
	 * @param int $user_id
	 *
	 * @throws ParseFatalException
	 */
	public static function delete(int $user_id):void {

		// если связь не существует, то ничего не делаем
		try {
			$account_user_rel = Gateway_Db_LdapData_LdapAccountUserRel::getOneByUserID($user_id);
		} catch (RowNotFoundException) {
			return;
		}

		// удаляем связь
		Gateway_Db_LdapData_LdapAccountUserRel::delete($account_user_rel->uid);
	}
}