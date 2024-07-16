<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\LocaleTextNotFound;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\System\Locale;

/**
 * Смена почты
 */
class Domain_User_Action_Security_ChangeMail_Begin {

	/**
	 * Начинаем смену почты для короткого сценария
	 *
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function doShort(string $change_mail_story_map, string $session_unique, string $mail):Domain_User_Entity_ChangeMail_CodeStory {

		$confirm_code = generateConfirmCode();
		$mail_id      = generateUUID();

		$code_story = Domain_User_Entity_ChangeMail_CodeStory::createNewCodeStory(
			$change_mail_story_map, $mail, $mail,
			Domain_User_Entity_ChangeMail_CodeStory::STAGE_CODE_ADDED,
			Domain_User_Entity_ChangeMail_CodeStory::STATUS_SUCCESS,
			$mail_id, $confirm_code);
		$code_story->storeInSessionCache($session_unique, Domain_User_Entity_ChangeMail_Story::STAGE_SECOND);

		Gateway_Db_PivotMail_MailChangeViaCodeStory::insert($code_story->getCodeStoryData());

		return $code_story;
	}

	/**
	 * Начинаем смену почты для полного сценария
	 *
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function doFull(string $change_mail_story_map, string $session_unique, string $mail):Domain_User_Entity_ChangeMail_CodeStory {

		$confirm_code = generateConfirmCode();
		$mail_id      = generateUUID();

		// отправляем проверочный код на почту
		self::_sendConfirmCode($confirm_code, $mail_id, $mail);
		Domain_User_Entity_CachedConfirmCode::storeChangeMailParams($mail, $confirm_code, Domain_User_Entity_ChangeMail_CodeStory::EXPIRE_AFTER);

		$code_story = Domain_User_Entity_ChangeMail_CodeStory::createNewCodeStory(
			$change_mail_story_map, $mail, $mail,
			Domain_User_Entity_ChangeMail_CodeStory::STAGE_START,
			Domain_User_Entity_ChangeMail_CodeStory::STATUS_ACTIVE,
			$mail_id, $confirm_code);
		$code_story->storeInSessionCache($session_unique, Domain_User_Entity_ChangeMail_Story::STAGE_FIRST);

		Gateway_Db_PivotMail_MailChangeViaCodeStory::insert($code_story->getCodeStoryData());

		return $code_story;
	}

	/**
	 * Отправляем проверочный код
	 *
	 * @throws \queryException
	 * @throws ParseFatalException
	 */
	protected static function _sendConfirmCode(string $confirm_code, string $mail_id, string $mail):array {

		// получаем конфиг с шаблонами для писем
		$config = getConfig("LOCALE_TEXT");

		[$title, $content] = Type_Mail_Content::make($config, Type_Mail_Content::TEMPLATE_MAIL_CHANGE, Locale::LOCALE_RUSSIAN, [
			"confirm_code" => addConfirmCodeDash($confirm_code),
		]);

		// добавляем задачу на отправку
		Type_Mail_Queue::addTask($mail_id, $mail, $title, $content, []);

		return [$confirm_code, $mail_id];
	}
}