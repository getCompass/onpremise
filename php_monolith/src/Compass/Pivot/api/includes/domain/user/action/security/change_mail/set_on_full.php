<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\LocaleTextNotFound;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\System\Locale;

/**
 * Заканчиваем первый этап
 */
class Domain_User_Action_Security_ChangeMail_SetOnFull {

	/**
	 * Шлем код на новую почту
	 *
	 * @throws \queryException
	 * @throws ParseFatalException
	 */
	public static function do(Domain_User_Entity_ChangeMail_Story $story, Domain_User_Entity_ChangeMail_CodeStory $code_story, string $session_uniq, string $mail):array {

		$confirm_code = generateConfirmCode();
		$mail_id      = generateUUID();

		// отправляем проверочный код на почту
		self::_sendConfirmCode($confirm_code, $mail_id, $mail);
		Domain_User_Entity_CachedConfirmCode::storeChangeMailParams($mail, $confirm_code, Domain_User_Entity_ChangeMail_CodeStory::EXPIRE_AFTER);

		// обновляем запись истории
		$code_story      = Domain_User_Entity_ChangeMail_CodeStory::createNewCodeStory(
			$story->getStoryMap(), $code_story->getMail(), $mail,
			Domain_User_Entity_ChangeMail_CodeStory::STAGE_START,
			Domain_User_Entity_ChangeMail_CodeStory::STATUS_ACTIVE,
			$mail_id, $confirm_code
		);
		$code_story_data = $code_story->getCodeStoryData();
		Gateway_Db_PivotMail_MailChangeViaCodeStory::setById($code_story_data->change_mail_story_id, $code_story_data->mail, [
			"mail_new"       => $code_story_data->mail_new,
			"status"         => $code_story_data->status,
			"stage"          => $code_story_data->stage,
			"resend_count"   => $code_story_data->resend_count,
			"error_count"    => $code_story_data->error_count,
			"updated_at"     => $code_story_data->updated_at,
			"next_resend_at" => $code_story_data->next_resend_at,
			"message_id"     => $code_story_data->message_id,
			"code_hash"      => $code_story_data->code_hash,
		]);

		// обновляем story
		$set = [
			"stage"      => Domain_User_Entity_ChangeMail_Story::STAGE_SECOND,
			"updated_at" => $code_story_data->updated_at,
		];
		$story->updateEntity($story->getStoryData(), $set);

		$code_story->storeInSessionCache($session_uniq, Domain_User_Entity_ChangeMail_Story::STAGE_SECOND);

		return [$story, $code_story, Domain_User_Scenario_Api_Security_Mail::SCENARIO_FULL_CHANGE];
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