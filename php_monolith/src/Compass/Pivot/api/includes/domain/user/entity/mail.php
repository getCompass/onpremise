<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Gateway\RowNotFoundException;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Server\ServerProvider;

/**
 * Класс для взаимодействия с электронной почтой.
 */
class Domain_User_Entity_Mail {

	/**
	 * Получить запись по электронной почте
	 *
	 * @throws Domain_User_Exception_Mail_NotFound
	 */
	public static function get(string $mail):Struct_Db_PivotMail_MailUniq {

		try {
			return Gateway_Db_PivotMail_MailUniqList::getOne(Type_Hash_Mail::makeHash($mail));
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {
			throw new Domain_User_Exception_Mail_NotFound("there is no record for passed mail");
		}
	}

	/**
	 * Получаем запись с почтой пользователя
	 *
	 * @return Struct_Db_PivotMail_MailUniq
	 * @throws Domain_User_Exception_Mail_NotFound
	 */
	public static function getUserMail(string $mail):Struct_Db_PivotMail_MailUniq {

		try {
			return Gateway_Db_PivotMail_MailUniqList::getOneWithUserId(Type_Hash_Mail::makeHash($mail));
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {
			throw new Domain_User_Exception_Mail_NotFound("there is no record for passed mail");
		}
	}

	/**
	 * Получить user_id владельца почты
	 *
	 * @throws Domain_User_Exception_Mail_NotFound
	 */
	public static function getUserIdByMail(string $mail):int {

		return self::getUserMail($mail)->user_id;
	}

	/**
	 * Получить запись на обновление по электронной почте
	 *
	 * @throws Domain_User_Exception_Mail_NotFound
	 */
	public static function getForUpdate(string $mail):Struct_Db_PivotMail_MailUniq {

		try {
			return Gateway_Db_PivotMail_MailUniqList::getForUpdate(Type_Hash_Mail::makeHash($mail));
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {
			throw new Domain_User_Exception_Mail_NotFound("there is no record for passed mail");
		}
	}

	/**
	 * Получить запись на обновление по электронной почте
	 *
	 * @throws Domain_User_Exception_Mail_NotFound
	 */
	public static function getByUserId(int $user_id):string {

		try {
			return Gateway_Db_PivotUser_UserSecurity::getOne($user_id)->mail;
		} catch (\cs_RowIsEmpty) {
			throw new Domain_User_Exception_Mail_NotFound("there is no record for passed mail");
		}
	}

	/**
	 * Обновляем пароль
	 *
	 * @throws \parseException
	 */
	public static function updatePassword(string $mail, string $password_hash):void {

		Gateway_Db_PivotMail_MailUniqList::set(Type_Hash_Mail::makeHash($mail), [
			"password_hash" => $password_hash,
			"updated_at"    => time(),
		]);
	}

	/**
	 * сменяем почту пользователю
	 *
	 * @throws Domain_User_Exception_Mail_BelongAnotherUser
	 * @throws RowNotFoundException
	 * @throws \parseException
	 * @throws \returnException
	 * @long
	 */
	public static function change(int $user_id, string $old_mail, string $new_mail):void {

		$old_mail_hash = Type_Hash_Mail::makeHash($old_mail);
		$new_mail_hash = Type_Hash_Mail::makeHash($new_mail);

		// начинаем транзакцию
		Gateway_Db_PivotMail_Main::beginTransaction();

		try {
			$new_mail_uniq = Gateway_Db_PivotMail_MailUniqList::getForUpdate(Type_Hash_Mail::makeHash($new_mail));
		} catch (RowNotFoundException) {

			Gateway_Db_PivotMail_MailUniqList::insertIgnore(new Struct_Db_PivotMail_MailUniq(
				mail_hash: $new_mail_hash,
				user_id: 0,
				has_sso_account: false,
				created_at: time(),
				updated_at: 0,
				password_hash: "",
			));
			$new_mail_uniq = Gateway_Db_PivotMail_MailUniqList::getForUpdate(Type_Hash_Mail::makeHash($new_mail));
		}

		// если почта принадлежит другому
		if ($new_mail_uniq->user_id !== 0) {

			Gateway_Db_PivotMail_Main::rollback();
			throw new Domain_User_Exception_Mail_BelongAnotherUser("mail belong another user");
		}

		// получаем запись с текущей почты, чтобы перенести пароль
		$old_mail_uniq = Gateway_Db_PivotMail_MailUniqList::getOne($old_mail_hash);

		// закрепляем новую почту за пользователем
		Gateway_Db_PivotMail_MailUniqList::set($new_mail_hash, [
			"user_id"       => $user_id,
			"password_hash" => $old_mail_uniq->password_hash,
		]);

		// открепляем старую почту
		Gateway_Db_PivotMail_MailUniqList::set($old_mail_hash, [
			"user_id"       => 0,
			"password_hash" => "",
		]);

		// обновляем user_security
		Gateway_Db_PivotUser_UserSecurity::set($user_id, [
			"mail" => $new_mail,
		]);

		Gateway_Db_PivotMail_Main::commitTransaction();
	}

	/**
	 * Указывает на наличие почты
	 */
	public static function hasMail(Struct_Db_PivotUser_UserSecurity $user_security):bool {

		if (mb_strlen($user_security->mail) > 0) {
			return true;
		}

		return false;
	}

	/**
	 * Получаем маску почты
	 */
	public static function getMailMask(string $mail):string {

		return (new \BaseFrame\System\Mail($mail))->obfuscate();
	}

	/**
	 * Проверяем что почты еще нет у пользователя
	 *
	 * @throws Domain_User_Exception_Mail_AlreadyExist
	 */
	public static function assertNotExistMail(Struct_Db_PivotUser_UserSecurity $user_security):void {

		if (self::hasMail($user_security)) {
			throw new Domain_User_Exception_Mail_AlreadyExist("mail already exist in user");
		}
	}

	/**
	 * Проверяем что почта есть у пользователя
	 *
	 * @throws Domain_User_Exception_Mail_NotFound
	 */
	public static function assertAlreadyExistMail(Struct_Db_PivotUser_UserSecurity $user_security):void {

		if (self::hasMail($user_security)) {
			return;
		}

		throw new Domain_User_Exception_Mail_NotFound("mail not exist in user");
	}

	/**
	 * проверяем что почта не занята
	 *
	 * @throws Domain_User_Exception_Mail_IsTaken
	 */
	public static function assertMailNotTaken(string $mail):void {

		// проверяем что данная почта никем не занята
		try {
			$user_mail = self::get($mail);
		} catch (Domain_User_Exception_Mail_NotFound) {

			// почта никем не занята, завершаем проверку
			return;
		}

		if ($user_mail->user_id > 0) {
			throw new Domain_User_Exception_Mail_IsTaken("mail is taken");
		}
	}

	/**
	 * проверяем что почта не занята другим пользователем
	 *
	 * @throws Domain_User_Exception_Mail_IsTaken
	 */
	public static function assertMailNotTakenAnotherUser(string $mail, int $user_id):void {

		// проверяем что данная почта никем не занята
		try {
			$user_mail = self::get($mail);
		} catch (Domain_User_Exception_Mail_NotFound) {

			// почта никем не занята, завершаем проверку
			return;
		}

		if ($user_mail->user_id > 0 && $user_mail->user_id !== $user_id) {
			throw new Domain_User_Exception_Mail_IsTaken("mail is taken another user");
		}
	}

	/**
	 * проверяем, что сценарий short допустим
	 *
	 * @throws ParseFatalException|Domain_User_Exception_Mail_ScenarioNotAllowed
	 */
	public static function assertShortScenario():void {

		// игнорируем проверку в тестах, если нужно
		if (ServerProvider::isTest() && Type_System_Testing::isIgnoreMailScenarioException()) {
			return;
		}

		// бросаем ошибку в тестах если нужно
		if (ServerProvider::isTest() && Type_System_Testing::isMailScenarioException()) {
			throw new Domain_User_Exception_Mail_ScenarioNotAllowed("short scenario not allowed");
		}

		if (Domain_User_Entity_Auth_Config::isMailAuthorization2FAEnabled()) {
			throw new Domain_User_Exception_Mail_ScenarioNotAllowed("short scenario not allowed");
		}
	}

	/**
	 * проверяем, что сценарий full допустим
	 *
	 * @throws ParseFatalException
	 */
	public static function assertFullScenario():void {

		// игнорируем проверку в тестах, если нужно
		if (ServerProvider::isTest() && Type_System_Testing::isIgnoreMailScenarioException()) {
			return;
		}

		// бросаем ошибку в тестах если нужно
		if (ServerProvider::isTest() && Type_System_Testing::isMailScenarioException()) {
			throw new Domain_User_Exception_Mail_ScenarioNotAllowed("full scenario not allowed");
		}

		if (!Domain_User_Entity_Auth_Config::isMailAuthorization2FAEnabled()) {
			throw new Domain_User_Exception_Mail_ScenarioNotAllowed("full scenario not allowed");
		}
	}

	/**
	 * отвязываем почту у пользователя
	 *
	 * @throws ParseFatalException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function unbind(int $user_id):void {

		// проверяем наличие почты
		$user_security = Gateway_Db_PivotUser_UserSecurity::getOne($user_id);

		// если почта не привязана, то ничего не делаем
		if ($user_security->mail === "") {
			return;
		}

		// хэш-сумма почты
		$mail_hash = Type_Hash_Mail::makeHash($user_security->mail);

		Gateway_Db_PivotMail_Main::beginTransaction();

		// открепляем почту
		Gateway_Db_PivotMail_MailUniqList::set($mail_hash, [
			"user_id"         => 0,
			"has_sso_account" => 0,
			"password_hash"   => "",
			"updated_at"      => time(),
		]);

		// обновляем user_security
		Gateway_Db_PivotUser_UserSecurity::set($user_id, [
			"mail"       => "",
			"updated_at" => time(),
		]);

		Gateway_Db_PivotMail_Main::commitTransaction();
	}

	/**
	 * проверяем, что пользователь не был зарегистрирован через SSO
	 *
	 * @throws Domain_User_Exception_Security_UserWasRegisteredBySso
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	public static function assertUserWasNotRegisteredBySso(int $user_id):void {

		if (Gateway_Socket_Federation::hasSsoUserRelationship($user_id) && !Domain_User_Entity_Auth_Config::isAuthorizationAlternativeEnabled()) {
			throw new Domain_User_Exception_Security_UserWasRegisteredBySso();
		}
	}
}