<?php

namespace Compass\Pivot;

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
}