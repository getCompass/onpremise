<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\System\Locale;
use Error;

/**
 * класс описывает все действия связанные с паролем связанным с почтой
 * @package Compass\Pivot
 */
class Domain_User_Action_Password_Mail {

	/**
	 * меняем пароль
	 *
	 * @param int      $user_id
	 * @param string   $mail
	 * @param string   $password
	 * @param string   $password_new
	 * @param int|null $password_mail_story_id
	 *
	 * @throws Domain_User_Exception_Password_Mismatch
	 * @throws Domain_User_Exception_Mail_NotFound
	 */
	public static function changePassword(int $user_id, string $mail, string $password, string $password_new, int|null $password_mail_story_id):void {

		/** начало транзакции */
		Gateway_Db_PivotMail_MailUniqList::beginTransaction();

		// меняем пароль
		try {
			self::_changePasswordForTransaction($user_id, $mail, $password, $password_new, $password_mail_story_id);
		} catch (Domain_User_Exception_Password_Mismatch|ParseFatalException|\returnException|Error $e) {

			Gateway_Db_PivotMail_MailUniqList::rollback();
			self::incErrorCount($password_mail_story_id);
			throw $e;
		}

		Gateway_Db_PivotMail_MailUniqList::commitTransaction();
		/** конец транзакции */
	}

	/**
	 * меняем пароль, функция для транзакции
	 *
	 * @throws Domain_User_Exception_Mail_NotFound
	 * @throws Domain_User_Exception_Password_Mismatch
	 */
	protected static function _changePasswordForTransaction(int $user_id, string $mail, string $password, string $password_new, int|null $password_mail_story):void {

		// получаем запись с паролем
		$user_mail = Domain_User_Entity_Mail::getForUpdate($mail);

		// если не совпал user_id
		if ($user_mail->user_id !== $user_id) {
			throw new Domain_User_Exception_UserNotAuthorized();
		}

		// сверяем пароли
		Domain_User_Entity_Password::assertPassword($password, $user_mail);

		// обновляем пароль
		Domain_User_Entity_Mail::updatePassword($mail, Domain_User_Entity_Password::makeHash($password_new));

		// обновляем статус story на успешно выполненный
		self::updateStatusById($password_mail_story, Domain_User_Entity_PasswordMail_Story::STATUS_SUCCESS);
	}

	/**
	 * Обновляем запись по password_mail_story_id
	 *
	 * @param int|null $password_mail_story_id
	 * @param int      $status
	 *
	 * @throws \parseException
	 */
	public static function updateStatusById(int|null $password_mail_story_id, int $status):void {

		$set = [
			"status"     => $status,
			"updated_at" => time(),
		];
		Gateway_Db_PivotMail_MailPasswordStory::setById($password_mail_story_id, $set);
	}

	/**
	 * инкрементим ошибку
	 *
	 * @param int|null $password_mail_story_id
	 *
	 */
	public static function incErrorCount(int|null $password_mail_story_id):void {

		// пишем в story ошибку
		$set = [
			"error_count" => "error_count + 1",
			"updated_at"  => time(),
		];
		Gateway_Db_PivotMail_MailPasswordStory::setById($password_mail_story_id, $set);
	}

	/**
	 * Начинаем story процесса
	 *
	 * @throws \queryException
	 */
	public static function beginStory(int $user_id, string $session_uniq, int $type):Domain_User_Entity_PasswordMail_Story {

		try {

			$password_mail_story = Domain_User_Entity_PasswordMail_Story::getFromSessionCache($session_uniq, $type)
				->assertUserAuthorized($user_id)
				->assertNotExpired()
				->assertActive()
				->assertNotSuccess();
		} catch (cs_CacheIsEmpty|Domain_User_Exception_Password_StoryIsExpired
		|Domain_User_Exception_Password_StoryIsNotActive|Domain_User_Exception_Password_StoryIsSuccess) {

			// создаем новый процесс
			$password_mail_story    = Domain_User_Entity_PasswordMail_Story::createNewStory(
				$user_id, $session_uniq, $type
			);
			$password_mail_story_id = Gateway_Db_PivotMail_MailPasswordStory::insert($password_mail_story->getStoryData());
			$password_mail_story    = Domain_User_Entity_PasswordMail_Story::updateStory(
				$password_mail_story->getStoryData(), ["password_mail_story_id" => $password_mail_story_id]
			);
			$password_mail_story->storeInSessionCache($session_uniq, $type);
		}

		return $password_mail_story;
	}

	/**
	 * удаляем story процесса
	 *
	 * @param string $session_uniq
	 * @param int    $type
	 */
	public static function deleteStory(string $session_uniq, int $type):void {

		Domain_User_Entity_PasswordMail_Story::deleteSessionCache($session_uniq, $type);
	}

	/**
	 * Начинаем сброс пароля
	 *
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function beginResetPassword(int $password_mail_story_id, string $session_unique, string $mail):Domain_User_Entity_PasswordMail_CodeStory {

		// отправляем проверочный код на почту
		$confirm_code = generateConfirmCode();
		$mail_id      = generateUUID();
		self::_sendConfirmCode($confirm_code, $mail_id, Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_RESET_PASSWORD_BY_MAIL, $mail);
		Domain_User_Entity_CachedConfirmCode::storeResetPasswordMailParams($mail, $confirm_code, Domain_User_Entity_Security_AddMail_CodeStory::EXPIRE_AFTER);

		// создаем все необходимые сущности
		$code_story = Domain_User_Entity_PasswordMail_CodeStory::createNewStory($password_mail_story_id, $mail, $confirm_code, $mail_id);
		Gateway_Db_PivotMail_MailPasswordViaCodeStory::insert($code_story->getStoryData());
		$code_story->storeInSessionCache($session_unique, Domain_User_Entity_PasswordMail_Story::TYPE_RESET_PASSWORD);

		return $code_story;
	}

	/**
	 * Отправляем проверочный код
	 *
	 * @throws \queryException
	 * @throws ParseFatalException
	 */
	protected static function _sendConfirmCode(string $confirm_code, string $mail_id, int $auth_type, string $mail):array {

		// получаем конфиг с шаблонами для писем
		$config = getConfig("LOCALE_TEXT");

		// формируем заголовок и содержимое письма
		$template = match ($auth_type) {
			Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_RESET_PASSWORD_BY_MAIL => Type_Mail_Content::TEMPLATE_MAIL_RESTORE,
			default                                                              => throw new ParseFatalException("unexpected auth story type [{$auth_type}]"),
		};

		[$title, $content] = Type_Mail_Content::make($config, $template, Locale::LOCALE_RUSSIAN, [
			"confirm_code" => addConfirmCodeDash($confirm_code),
		]);

		// добавляем задачу на отправку
		Type_Mail_Queue::addTask($mail_id, $mail, $title, $content, []);

		return [$confirm_code, $mail_id];
	}

	/**
	 * сбрасываем пароль
	 *
	 * @throws Domain_User_Exception_Mail_NotFound
	 * @throws \parseException
	 * @throws Domain_User_Exception_UserNotAuthorized
	 */
	public static function resetPassword(int $user_id, string $mail, string $password):void {

		// получаем запись с паролем
		$user_mail = Domain_User_Entity_Mail::get($mail);

		// если не совпал user_id
		if ($user_mail->user_id !== $user_id) {
			throw new Domain_User_Exception_UserNotAuthorized();
		}

		// обновляем пароль
		Domain_User_Entity_Mail::updatePassword($mail, Domain_User_Entity_Password::makeHash($password));
	}
}