<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\LocaleTextNotFound;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\System\Locale;

/**
 *
 */
class Domain_User_Action_Security_ResetPasswordMail_ResendCode {

	/**
	 * Выполняем переотправку проверочного кода
	 *
	 * @return Domain_User_Entity_PasswordMail_CodeStory
	 * @throws Domain_User_Exception_Mail_StoryIsExpired
	 * @throws \queryException
	 */
	public static function do(Domain_User_Entity_PasswordMail_CodeStory $code_story):Domain_User_Entity_PasswordMail_CodeStory {

		$story_code_data = $code_story->getStoryData();

		try {
			$confirm_code = Domain_User_Entity_CachedConfirmCode::getConfirmCodeByResetPasswordMail($story_code_data->mail);
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
		Type_Mail_Queue::addTask($message_id, $story_code_data->mail, $title, $content, []);

		// формируем hash проверочного кода
		try {
			$code_hash = Type_Hash_Code::makeHash($confirm_code);
		} catch (cs_IncorrectSaltVersion) {
			throw new ParseFatalException("fatal error make hash");
		}

		// обновляем запись с историей
		$set                = [
			"resend_count"   => $story_code_data->resend_count + 1,
			"next_resend_at" => time() + Domain_User_Entity_PasswordMail_Story::NEXT_RESEND_AFTER,
			"message_id"     => $message_id,
			"code_hash"      => $code_hash,
			"updated_at"     => time(),
		];
		$updated_code_story = $code_story->updateEntity($story_code_data, $set);
		Domain_User_Entity_CachedConfirmCode::storeResetPasswordMailParams($story_code_data->mail, $confirm_code, Domain_User_Entity_PasswordMail_Story::EXPIRE_AFTER);

		return $updated_code_story;
	}
}