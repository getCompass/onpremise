<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\LocaleTextNotFound;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\System\Locale;
use PhpParser\Error;

/**
 * класс описывает все действия связанные с паролем связанным с почтой
 * @package Compass\Pivot
 */
class Domain_User_Action_Mail_Add {

	/**
	 * устанавливаем пароль при добавлении почты по короткому сценарию
	 *
	 * @throws Domain_User_Exception_AuthStory_Mail_DomainNotAllowed
	 * @throws Domain_User_Exception_Mail_AlreadyExist
	 * @throws Domain_User_Exception_Mail_IsTaken
	 * @throws Domain_User_Exception_Mail_StoryIsExpired
	 * @throws Domain_User_Exception_Mail_StoryIsNotActive
	 * @throws Domain_User_Exception_Mail_StoryIsSuccess
	 * @throws Domain_User_Exception_Mail_StoryNotFound
	 * @throws Domain_User_Exception_UserNotAuthorized
	 */
	public static function doSetPasswordOnShort(int $user_id, string $add_mail_story_map, string $mail, string $password):void {

		/** начало транзакции */
		Gateway_Db_PivotMail_Main::beginTransaction();

		try {
			self::_doSetPasswordOnShortForTransaction($user_id, $add_mail_story_map, $mail, $password);
		} catch (Domain_User_Exception_AuthStory_Mail_DomainNotAllowed|Domain_User_Exception_Mail_IsTaken|Domain_User_Exception_Mail_AlreadyExist
		|Domain_User_Exception_Mail_AlreadyExist|Domain_User_Exception_UserNotAuthorized|Domain_User_Exception_Mail_StoryIsExpired
		|Domain_User_Exception_Mail_StoryIsNotActive|Domain_User_Exception_Mail_StoryIsSuccess|Domain_User_Exception_Mail_StoryNotFound
		|Domain_User_Exception_Mail_StoryIsNotActive $e) {

			Gateway_Db_PivotMail_Main::rollback();
			self::incErrorCount($add_mail_story_map);
			throw $e;
		}

		Gateway_Db_PivotMail_Main::commitTransaction();
		/** конец транзакции */

		// удаляем процесс из кеша
		Domain_User_Entity_Security_AddMail_Story::deleteSessionCache($mail);
	}

	/**
	 * устанавливаем пароль при добавлении почты по короткому сценарию (функция для транзакции)
	 *
	 * @throws Domain_User_Exception_Mail_AlreadyExist
	 * @throws Domain_User_Exception_Mail_IsTaken
	 * @throws Domain_User_Exception_Mail_StoryIsExpired
	 * @throws Domain_User_Exception_Mail_StoryIsNotActive
	 * @throws Domain_User_Exception_Mail_StoryIsSuccess
	 * @throws Domain_User_Exception_Mail_StoryNotFound
	 * @throws Domain_User_Exception_UserNotAuthorized
	 */
	protected static function _doSetPasswordOnShortForTransaction(int $user_id, string $add_mail_story_map, string $mail, string $password):void {

		$add_mail_story      = Domain_User_Entity_Security_AddMail_Story::getForUpdate($add_mail_story_map, $mail)
			->assertActive()->assertNotExpire()->assertNotSuccess();
		$add_mail_story_data = $add_mail_story->getStoryData();

		// если не совпал user_id
		if ($add_mail_story_data->user_id !== $user_id) {
			throw new Domain_User_Exception_UserNotAuthorized();
		}

		// проверяем что у пользователя еще нет почты
		try {
			$user_security = Gateway_Db_PivotUser_UserSecurity::getOne($user_id);
		} catch (\cs_RowIsEmpty) {
			throw new ParseFatalException("user not found");
		}
		Domain_User_Entity_Mail::assertNotExistMail($user_security);

		// проверяем что данная почта никем не занята
		Domain_User_Entity_Mail::assertMailNotTaken($mail);

		// добавляем запись почты
		$action_time   = time();
		$mail_hash     = Type_Hash_Mail::makeHash($mail);
		$password_hash = Domain_User_Entity_Password::makeHash($password);
		Gateway_Db_PivotMail_MailUniqList::insertOrUpdate(new Struct_Db_PivotMail_MailUniq($mail_hash, $user_id, false, $action_time, 0, $password_hash));
		Gateway_Db_PivotUser_UserSecurity::set($user_id, [
			"mail"       => $mail,
			"updated_at" => $action_time,
		]);

		// обновляем историю
		self::updateStatus($add_mail_story_map, Domain_User_Entity_Security_AddMail_Story::STATUS_SUCCESS);
	}

	/**
	 * устанавливаем пароль при добавлении почты по полному сценарию (второй шаг)
	 *
	 * @return array
	 * @throws Domain_User_Exception_Mail_AlreadyExist
	 * @throws Domain_User_Exception_Mail_IsTaken
	 * @throws Domain_User_Exception_Mail_StoryIsExpired
	 * @throws Domain_User_Exception_Mail_StoryIsSuccess
	 * @throws Domain_User_Exception_Mail_StoryNotFound
	 * @throws Domain_User_Exception_UserNotAuthorized
	 * @throws \queryException
	 *
	 * @long большой try-catch
	 */
	public static function doSetPasswordOnFullAdd(int $user_id, string $add_mail_story_map, string $mail, string $password):array {

		$story      = Domain_User_Entity_Security_AddMail_Story::get($add_mail_story_map, $mail)
			->assertNotSuccess()->assertNotExpire()->assertUserAuthorized($user_id);
		$story_code = Domain_User_Entity_Security_AddMail_CodeStory::get($add_mail_story_map, $mail)->assertNotSuccess();

		// проверяем что у пользователя еще нет почты
		try {
			$user_security = Gateway_Db_PivotUser_UserSecurity::getOne($user_id);
		} catch (\cs_RowIsEmpty) {
			throw new ParseFatalException("user not found");
		}
		Domain_User_Entity_Mail::assertNotExistMail($user_security);

		// проверяем что данная почта никем не занята
		Domain_User_Entity_Mail::assertMailNotTaken($mail);

		// если код еще не отправлен
		try {
			[$confirm_code,] = Domain_User_Entity_CachedConfirmCode::getAddMailFullScenarioParams($mail);
		} catch (cs_CacheIsEmpty) {

			// формируем проверочный код и получаем конфиг с шаблонами для писем
			$confirm_code = generateConfirmCode();
			$message_id   = generateUUID();
			$config       = getConfig("LOCALE_TEXT");

			// формируем заголовок и содержимое письма
			[$title, $content] = Type_Mail_Content::make($config, Type_Mail_Content::TEMPLATE_MAIL_ADD, Locale::LOCALE_RUSSIAN, [
				"confirm_code" => addConfirmCodeDash($confirm_code),
			]);

			// добавляем задачу на отправку
			Type_Mail_Queue::addTask($message_id, $mail, $title, $content, []);

			// формируем hash проверочного кода
			try {
				$code_hash = Type_Hash_Code::makeHash($confirm_code);
			} catch (cs_IncorrectSaltVersion) {
				throw new ParseFatalException("fatal error make hash");
			}

			// сохраняем проверочный код
			$set = [
				"stage"          => Domain_User_Entity_Security_AddMail_Story::STAGE_WRONG_CODE,
				"resend_count"   => 0,
				"next_resend_at" => time() + Domain_User_Entity_Security_AddMail_CodeStory::NEXT_RESEND_AFTER,
				"message_id"     => $message_id,
				"code_hash"      => $code_hash,
				"updated_at"     => time(),
			];
			Gateway_Db_PivotMail_MailAddViaCodeStory::set($add_mail_story_map, $mail, $set);
			$story_code = Domain_User_Entity_Security_AddMail_CodeStory::updateStoryData($story_code->getCodeStoryData()->mail, $story_code->getCodeStoryData(), $set);

			// обновляем историю
			self::updateStage($add_mail_story_map, Domain_User_Entity_Security_AddMail_Story::STAGE_WRONG_CODE);
		}

		// сохраняем в кеш
		Domain_User_Entity_CachedConfirmCode::storeAddMailFullScenarioParams($mail, $confirm_code, $password, Domain_User_Entity_Security_AddMail_CodeStory::EXPIRE_AFTER);

		return [$story, $story_code];
	}

	/**
	 * подтверждаем кодом добавлении почты по полному сценарию
	 *
	 * @throws Domain_User_Exception_AuthStory_Mail_DomainNotAllowed
	 * @throws Domain_User_Exception_Mail_AlreadyExist
	 * @throws Domain_User_Exception_Mail_CodeErrorCountExceeded
	 * @throws Domain_User_Exception_Mail_IsTaken
	 * @throws Domain_User_Exception_Mail_StoryIsNotActive
	 * @throws Domain_User_Exception_Mail_StoryIsSuccess
	 * @throws Domain_User_Exception_Mail_StoryNotEqualStage
	 * @throws Domain_User_Exception_Mail_StoryNotFound
	 * @throws Domain_User_Exception_UserNotAuthorized
	 * @throws cs_CacheIsEmpty
	 * @throws cs_WrongCode
	 */
	public static function doConfirmCodeOnFullAdd(int $user_id, string $add_mail_story_map, string $mail, string $code):void {

		/** начало транзакции */
		Gateway_Db_PivotMail_Main::beginTransaction();

		try {
			self::_doConfirmCodeOnFullAddForTransaction($user_id, $add_mail_story_map, $mail, $code);
		} catch (Domain_User_Exception_AuthStory_Mail_DomainNotAllowed|Domain_User_Exception_Mail_IsTaken|Domain_User_Exception_Mail_AlreadyExist
		|Domain_User_Exception_Mail_AlreadyExist|Domain_User_Exception_UserNotAuthorized
		|ParseFatalException|\returnException|Error $e) {

			Gateway_Db_PivotMail_Main::rollback();
			Domain_User_Action_Mail_Add::incErrorCount($add_mail_story_map);
			throw $e;
		} catch (cs_WrongCode) {

			Gateway_Db_PivotMail_Main::rollback();
			[$available_attempts, $next_resend] = Domain_User_Action_Security_AddMail_IncrementErrorWrongCode::do($add_mail_story_map, $mail);
			throw new cs_WrongCode($available_attempts, $next_resend);
		}

		Gateway_Db_PivotMail_Main::commitTransaction();
		/** конец транзакции */
	}

	/**
	 * подтверждаем кодом добавлении почты по полному сценарию (функция для транзакции)
	 *
	 * @throws Domain_User_Exception_Mail_AlreadyExist
	 * @throws Domain_User_Exception_Mail_CodeErrorCountExceeded
	 * @throws Domain_User_Exception_Mail_IsTaken
	 * @throws Domain_User_Exception_Mail_StoryIsNotActive
	 * @throws Domain_User_Exception_Mail_StoryIsSuccess
	 * @throws Domain_User_Exception_Mail_StoryNotEqualStage
	 * @throws Domain_User_Exception_Mail_StoryNotFound
	 * @throws cs_CacheIsEmpty
	 * @throws cs_WrongCode
	 */
	protected static function _doConfirmCodeOnFullAddForTransaction(int $user_id, string $add_mail_story_map, string $mail, string $code):void {

		$add_mail_story = Domain_User_Entity_Security_AddMail_Story::getForUpdate($add_mail_story_map, $mail);

		// проверяем что у пользователя еще нет почты
		try {
			$user_security = Gateway_Db_PivotUser_UserSecurity::getOne($user_id);
		} catch (\cs_RowIsEmpty) {
			throw new ParseFatalException("user not found");
		}
		Domain_User_Entity_Mail::assertNotExistMail($user_security);

		// проверяем что данная почта никем не занята
		Domain_User_Entity_Mail::assertMailNotTaken($mail);

		// получаем запись c проверочным кодом
		$add_mail_code_story = Domain_User_Entity_Security_AddMail_CodeStory::get($add_mail_story_map, $mail);

		// делаем проверки истории, и проверяем переданные проверочный код
		$add_mail_code_story->assertNotSuccess()->assertActive()->assertEnteringCodeStage()->assertErrorCountNotExceeded()->assertEqualCode($code);

		// получаем запись из кеша с паролем
		[, $password] = Domain_User_Entity_CachedConfirmCode::getAddMailFullScenarioParams($mail);

		// добавляем запись почты
		$action_time   = time();
		$mail_hash     = Type_Hash_Mail::makeHash($mail);
		$password_hash = Domain_User_Entity_Password::makeHash($password);
		Gateway_Db_PivotMail_MailUniqList::insertOrUpdate(new Struct_Db_PivotMail_MailUniq($mail_hash, $user_id, false, $action_time, 0, $password_hash));
		Gateway_Db_PivotUser_UserSecurity::set($user_id, [
			"mail"       => $mail,
			"updated_at" => $action_time,
		]);

		// обновляем историю
		self::updateStatusCodeStore($add_mail_story_map, $mail, Domain_User_Entity_Security_AddMail_Story::STATUS_SUCCESS);
		$add_mail_story_data = $add_mail_story->getStoryData();
		self::updateStatusById($add_mail_story_data->add_mail_story_id, Domain_User_Entity_Security_AddMail_Story::STATUS_SUCCESS);
	}

	/**
	 * Обновляем статус по add_mail_story_id
	 */
	public static function updateStatusById(string $add_mail_story_id, int $status):void {

		$set = [
			"status"     => $status,
			"updated_at" => time(),
		];
		Gateway_Db_PivotMail_MailAddStory::setById($add_mail_story_id, $set);
	}

	/**
	 * Обновляем статус по add_mail_story_id
	 */
	public static function updateStatus(string $add_mail_story_map, int $status):void {

		try {
			$add_mail_story_id = Type_Pack_AddMailStory::getId($add_mail_story_map);
		} catch (\cs_UnpackHasFailed) {
			throw new ParseFatalException("fatal error parse map");
		}

		$set = [
			"status"     => $status,
			"updated_at" => time(),
		];
		Gateway_Db_PivotMail_MailAddStory::setById($add_mail_story_id, $set);
	}

	/**
	 * Обновляем stage по add_mail_story_id
	 */
	public static function updateStage(string $add_mail_story_map, int $stage):void {

		try {
			$add_mail_story_id = Type_Pack_AddMailStory::getId($add_mail_story_map);
		} catch (\cs_UnpackHasFailed) {
			throw new ParseFatalException("fatal error parse map");
		}

		$set = [
			"stage"      => $stage,
			"updated_at" => time(),
		];
		Gateway_Db_PivotMail_MailAddStory::setById($add_mail_story_id, $set);
	}

	/**
	 * инкрементим ошибку
	 */
	public static function incErrorCount(string $add_mail_story_map):void {

		try {
			$add_mail_story_id = Type_Pack_AddMailStory::getId($add_mail_story_map);
		} catch (\cs_UnpackHasFailed) {
			throw new ParseFatalException("fatal error parse map");
		}

		// пишем в story ошибку
		$set = [
			"error_count" => "error_count + 1",
			"updated_at"  => time(),
		];
		Gateway_Db_PivotMail_MailAddStory::setById($add_mail_story_id, $set);
	}

	/**
	 * Обновляем статус по add_mail_story_id
	 *
	 */
	public static function updateStatusCodeStore(string $add_mail_story_map, string $mail, int $status):void {

		$set = [
			"status"     => $status,
			"updated_at" => time(),
		];
		Gateway_Db_PivotMail_MailAddViaCodeStory::set($add_mail_story_map, $mail, $set);
	}
}