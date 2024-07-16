<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\BlockException;

/**
 * Класс для получения данных об истории действий подтверждения почты
 *
 * Class Domain_User_Entity_Confirmation_Mail_Mail
 */
class Domain_User_Entity_Confirmation_Mail_Mail {

	/**
	 * Обработать 2fa действие
	 *
	 * @param int          $user_id
	 * @param string       $session_uniq
	 * @param int          $action_type
	 * @param string|false $mail_password_story_confirm_key
	 *
	 * @throws BlockException
	 * @throws Domain_User_Exception_Confirmation_Mail_InvalidMailPasswordStoryKey
	 * @throws Domain_User_Exception_Confirmation_Mail_InvalidUser
	 * @throws Domain_User_Exception_Confirmation_Mail_IsExpired
	 * @throws Domain_User_Exception_Confirmation_Mail_IsInvalidType
	 * @throws Domain_User_Exception_Confirmation_Mail_IsNotConfirmed
	 * @throws Domain_User_Exception_Confirmation_Mail_NotSuccess
	 * @throws ParseFatalException
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 * @throws cs_AnswerCommand
	 * @throws cs_TwoFaTypeIsInvalid
	 * @throws cs_UnknownKeyType
	 * @throws cs_blockException
	 */
	public static function handle(int $user_id, string $session_uniq, int $action_type, string|false $mail_password_story_confirm_key):void {

		if ($mail_password_story_confirm_key) {

			Domain_User_Action_Confirmation_Mail_Validate::do($user_id, $action_type, $mail_password_story_confirm_key);
			return;
		}

		self::_createPasswordConfirmStory($user_id, $session_uniq, $action_type);
	}

	/**
	 * Сгенерировать токен и выбросить команду
	 *
	 * @param int    $user_id
	 * @param string $session_uniq
	 * @param int    $action_type
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 * @throws cs_AnswerCommand
	 * @throws cs_TwoFaTypeIsInvalid
	 */
	protected static function _createPasswordConfirmStory(int $user_id, string $session_uniq, int $action_type):array {

		Domain_User_Entity_Confirmation_Main::assertTypeIsValid($action_type);

		// если есть старая попытка, которую нельзя использовать - создаем новую
		try {

			$mail_password_confirmation_story = Domain_User_Entity_Confirmation_Mail_Story::getFromSessionCache($session_uniq, $action_type);

			// проверяем наличие почты у пользователя
			$user_security = Gateway_Db_PivotUser_UserSecurity::getOne($user_id);
			Domain_User_Entity_Mail::assertAlreadyExistMail($user_security);

			$mail_password_confirmation_story
				->assertCorrectUser($user_id)
				->assertNotExpired()
				->assertActive()
				->assertNotSuccess();

		} catch (Domain_User_Exception_Confirmation_Mail_InvalidUser|Domain_User_Exception_Confirmation_Mail_IsNotActive|
		Domain_User_Exception_Confirmation_Mail_IsSuccess|Domain_User_Exception_Confirmation_Mail_IsExpired|Domain_User_Exception_CacheIsEmpty) {

			$mail_password_confirmation_story = Domain_User_Entity_Confirmation_Mail_Story::create($user_id, $session_uniq, $action_type);
		} catch (Domain_User_Exception_Mail_NotFound) {

			// очень ненормально, что мы не смогли найти мыло у пользователя, ведь тогда сюда мы бы не могли попасть ранее
			throw new ParseFatalException("Could not find mail stories for user $user_id");
		}

		$mail_password_output = self::_makeAnswerOutput($mail_password_confirmation_story);

		throw new cs_AnswerCommand("need_confirm_mail_password", $mail_password_output);
	}

	/**
	 * Подготовить ответ для команды
	 *
	 * @param Domain_User_Entity_Confirmation_Mail_Story $story
	 *
	 * @return array
	 * @throws \parseException
	 */
	protected static function _makeAnswerOutput(Domain_User_Entity_Confirmation_Mail_Story $story):array {

		return Type_Pack_Main::replaceMapWithKeys([
			"confirm_mail_password_story_map" => (string) Type_Pack_MailPasswordConfirmStory::doPack(
				$story->getMailPasswordConfirmInfo()->confirm_mail_password_story_id,
				$story->getMailPasswordConfirmInfo()->type,
				$story->getMailPasswordConfirmInfo()->created_at
			),
			"action_type"                     => (int) $story->getMailPasswordConfirmInfo()->type,
			"expires_at"                      => (int) $story->getMailPasswordConfirmInfo()->expires_at,
		]);
	}
}