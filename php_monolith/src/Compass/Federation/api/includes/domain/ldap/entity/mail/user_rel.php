<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\DBShardingNotFoundException;
use BaseFrame\Exception\Gateway\QueryFatalException;
use BaseFrame\Exception\Gateway\RowDuplicationException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс для работы со связью «LDAP аккаунт» – «Пользователь Compass»
 * @package Compass\Federation
 */
class Domain_Ldap_Entity_Mail_UserRel {

	public const MAIL_SOURCE_LDAP   = 1; // почта привязана через LDAP
	public const MAIL_SOURCE_MANUAL = 2; // почта привязана вручную

	public string $uid;
	public int    $mail_source;
	public string $mail;
	public int    $created_at;
	public int    $updated_at;

	protected function __construct() {
	}

	/**
	 * Создать или обновить запись
	 *
	 * @param string $uid
	 * @param int    $mail_source
	 * @param string $mail
	 * @param bool   $update_existing
	 *
	 * @return void
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function create(string $uid, int $mail_source, string $mail, bool $update_existing = false):void {

		$db_mail_user_rel = new Struct_Db_LdapData_MailUserRel(
			$uid,
			$mail_source,
			$mail,
			time(),
			0
		);

		$update_existing
			? Gateway_Db_LdapData_MailUserRel::insertOrUpdate($db_mail_user_rel)
			: Gateway_Db_LdapData_MailUserRel::insert($db_mail_user_rel);
	}

	/**
	 * Получить запись
	 *
	 * @param string $uid
	 *
	 * @return self
	 * @throws DBShardingNotFoundException
	 * @throws Domain_Ldap_Exception_Mail_UserRelNotFound
	 * @throws QueryFatalException
	 */
	public static function get(string $uid):self {

		try {
			$db_mail_user_rel = Gateway_Db_LdapData_MailUserRel::getOne($uid);
		} catch (RowNotFoundException) {
			throw new Domain_Ldap_Exception_Mail_UserRelNotFound();
		}

		$mail_user_rel              = new self();
		$mail_user_rel->uid         = $db_mail_user_rel->uid;
		$mail_user_rel->mail_source = $db_mail_user_rel->mail_source;
		$mail_user_rel->mail        = $db_mail_user_rel->mail;
		$mail_user_rel->created_at  = $db_mail_user_rel->created_at;
		$mail_user_rel->updated_at  = $db_mail_user_rel->updated_at;

		return $mail_user_rel;
	}

	/**
	 * Получить по адресу почты
	 */
	public static function getByMail(string $mail):self {

		try {
			$db_mail_user_rel = Gateway_Db_LdapData_MailUserRel::getByMail($mail);
		} catch (RowNotFoundException) {
			throw new Domain_Ldap_Exception_Mail_UserRelNotFound();
		}

		$mail_user_rel              = new self();
		$mail_user_rel->uid         = $db_mail_user_rel->uid;
		$mail_user_rel->mail_source = $db_mail_user_rel->mail_source;
		$mail_user_rel->mail        = $db_mail_user_rel->mail;
		$mail_user_rel->created_at  = $db_mail_user_rel->created_at;
		$mail_user_rel->updated_at  = $db_mail_user_rel->updated_at;

		return $mail_user_rel;
	}
}