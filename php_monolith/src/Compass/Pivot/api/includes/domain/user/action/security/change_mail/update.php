<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\InvalidMail;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * Меняем почту сразу
 */
class Domain_User_Action_Security_ChangeMail_Update {

	/**
	 * Подтверждаем почту
	 *
	 * @throws InvalidMail
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws Domain_User_Exception_Mail_Binding
	 * @throws Domain_User_Exception_Mail_NotFound
	 */
	public static function do(Domain_User_Entity_ChangeMail_Story $story, string $mail):void {

		$action_time   = time();
		$story_data    = $story->getStoryData();
		$user_id       = $story_data->user_id;
		$new_mail_obj  = new \BaseFrame\System\Mail($mail);
		$new_mail_hash = Type_Hash_Mail::makeHash($new_mail_obj->mail());
		$old_mail      = Domain_User_Entity_Mail::getByUserId($user_id);
		$old_mail_hash = Type_Hash_Mail::makeHash($old_mail);

		self::_updateMailUniq($user_id, $old_mail_hash, $new_mail_hash, $action_time);
		self::_updateUserSecurity($user_id, $new_mail_obj->mail(), $action_time);

		// отправляем ивент пользователю о смене почты
		Gateway_Bus_SenderBalancer::mailChanged($user_id, $mail);
	}

	/**
	 * Вставляем записи о смене почты
	 *
	 * @throws \parseException
	 */
	protected static function _updateUserSecurity(int $user_id, string $new_mail, int $action_time):void {

		Gateway_Db_PivotUser_UserSecurity::set($user_id, [
			"mail"       => $new_mail,
			"updated_at" => $action_time,
		]);
	}

	/**
	 * Выставляем старую почту безхозной, вставляем запись о новой
	 *
	 * @throws Domain_User_Exception_Mail_Binding
	 * @throws Domain_User_Exception_Mail_NotFound
	 * @throws ParseFatalException
	 * @throws \returnException
	 */
	protected static function _updateMailUniq(int $user_id, string $old_mail_hash, string $new_mail_hash, int $action_time):void {

		/** начало транзакции */
		Gateway_Db_PivotMail_MailUniqList::beginTransaction();

		try {

			// обновляем запись для старой почты
			$old_mail_uniq = static::_updatePreviousMailUniq($user_id, $old_mail_hash, $action_time);
		} catch (RowNotFoundException) {

			// такого не должно происходить, если произошло, ошибка где-то выше в логике
			Gateway_Db_PivotMail_MailUniqList::rollback();
			throw new Domain_User_Exception_Mail_NotFound("there is no mail record to update");
		} catch (Domain_User_Exception_Mail_Binding $e) {

			// не удалось отвязать старую почту
			Gateway_Db_PivotMail_MailUniqList::rollback();
			throw $e;
		}

		try {

			// обновляем запись для новой почты
			static::_updateNewMailUniq($user_id, $new_mail_hash, $old_mail_uniq->password_hash, $action_time);
		} catch (RowNotFoundException) {

			// записи нет, это нормально, скорее всего почта новая
			Gateway_Db_PivotMail_MailUniqList::insertOrUpdate(new Struct_Db_PivotMail_MailUniq(
				$new_mail_hash, $user_id, $old_mail_uniq->has_sso_account, time(), 0, $old_mail_uniq->password_hash
			));
		} catch (Domain_User_Exception_Mail_Binding $e) {

			// новую почту нельзя использовать
			Gateway_Db_PivotMail_MailUniqList::rollback();
			throw $e;
		}

		Gateway_Db_PivotMail_MailUniqList::commitTransaction();
		/** конец транзакции */
	}

	/**
	 * Обновляет запись для старой почты
	 *
	 * @throws RowNotFoundException
	 * @throws Domain_User_Exception_Mail_Binding
	 * @throws ParseFatalException
	 */
	protected static function _updatePreviousMailUniq(int $user_id, string $old_mail_hash, int $action_time):Struct_Db_PivotMail_MailUniq {

		// получаем запись на чтение с блокировкой
		$mail_uniq = Gateway_Db_PivotMail_MailUniqList::getForUpdate($old_mail_hash);

		// проверим на всякий, что можем отвязать почту
		// быть может он вообще не принадлежит пользователю
		if ($mail_uniq->user_id === 0 || $mail_uniq->user_id !== $user_id) {
			throw new Domain_User_Exception_Mail_Binding("mail doesn't belong to user");
		}

		// обновляем запись
		Gateway_Db_PivotMail_MailUniqList::set($old_mail_hash, [
			"user_id"       => 0,
			"updated_at"    => $action_time,
			"password_hash" => "",
		]);

		return $mail_uniq;
	}

	/**
	 * Обновляет запись для новой почты
	 *
	 * @throws Domain_User_Exception_Mail_Binding
	 * @throws ParseFatalException
	 * @throws RowNotFoundException
	 */
	protected static function _updateNewMailUniq(int $user_id, string $new_mail_hash, string $password_hash, int $action_time):void {

		// получаем запись на чтение с блокировкой
		$mail_uniq = Gateway_Db_PivotMail_MailUniqList::getForUpdate($new_mail_hash);

		// проверим, что почта на текущим момент ни за кем не закреплен
		if ($mail_uniq->user_id !== 0) {
			throw new Domain_User_Exception_Mail_Binding("mail belong to another user");
		}

		// обновляем запись
		Gateway_Db_PivotMail_MailUniqList::set($new_mail_hash, [
			"user_id"       => $user_id,
			"updated_at"    => $action_time,
			"password_hash" => $password_hash,
		]);
	}
}