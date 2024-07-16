<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\LocaleTextNotFound;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\System\Locale;

/**
 *
 */
class Domain_User_Action_Security_AddMail_ResendCode {

	/**
	 * Выполняем переотправку проверочного кода
	 *
	 * @param string                                  $add_mail_story_map
	 * @param string                                  $mail
	 * @param Struct_Db_PivotMail_MailAddViaCodeStory $code_story
	 *
	 * @return Domain_User_Entity_Security_AddMail_CodeStory
	 * @throws Domain_User_Exception_Mail_StoryIsExpired
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function do(string $add_mail_story_map, string $mail, Struct_Db_PivotMail_MailAddViaCodeStory $code_story):Domain_User_Entity_Security_AddMail_CodeStory {

		try {
			[$confirm_code, $password] = Domain_User_Entity_CachedConfirmCode::getAddMailFullScenarioParams($mail);
		} catch (cs_CacheIsEmpty) {
			throw new Domain_User_Exception_Mail_StoryIsExpired("story is expired");
		}

		// получаем конфиг с шаблонами для писем
		$message_id = generateUUID();
		$config     = getConfig("LOCALE_TEXT");

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

		// обновляем запись с историей
		$set                = [
			"stage"          => Domain_User_Entity_Security_AddMail_Story::STAGE_WRONG_CODE,
			"resend_count"   => $code_story->resend_count + 1,
			"next_resend_at" => time() + Domain_User_Entity_Security_AddMail_CodeStory::NEXT_RESEND_AFTER,
			"message_id"     => $message_id,
			"code_hash"      => $code_hash,
			"updated_at"     => time(),
		];
		$updated_code_story = Domain_User_Entity_Security_AddMail_CodeStory::updateStoryData($code_story->mail, $code_story, $set);
		Gateway_Db_PivotMail_MailAddViaCodeStory::set($add_mail_story_map, $mail, $set);
		Domain_User_Entity_CachedConfirmCode::storeAddMailFullScenarioParams($mail, $confirm_code, $password, Domain_User_Entity_Security_AddMail_CodeStory::EXPIRE_AFTER);

		return $updated_code_story;
	}
}