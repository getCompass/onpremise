<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * Класс для взаимодействия с электронной почтой.
 */
class Domain_User_Entity_Mail {

	/**
	 * получить запись по электронной почте
	 *
	 * @return Struct_Db_PivotMail_MailUniq
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
	 * получаем почту пользователя
	 */
	public static function getByUserId(int $user_id):string {

		return Gateway_Db_PivotUser_UserSecurity::getOne($user_id)->mail;
	}

	/**
	 * обновляем пароль
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
}