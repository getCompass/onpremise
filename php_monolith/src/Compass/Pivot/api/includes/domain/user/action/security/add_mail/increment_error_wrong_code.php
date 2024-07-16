<?php

namespace Compass\Pivot;

/**
 * Увеличиваем ошибку при подтверждении
 */
class Domain_User_Action_Security_AddMail_IncrementErrorWrongCode {

	/**
	 * Выполняем инкремент ошибки
	 *
	 * @return array
	 * @throws Domain_User_Exception_Mail_StoryNotFound
	 */
	public static function do(string $add_mail_story_map, string $mail):array {

		// получаем запись c проверочным кодом
		$add_mail_code_story = Domain_User_Entity_Security_AddMail_CodeStory::get($add_mail_story_map, $mail);

		// увеличиваем счетчик ошибок
		$story_code_data = $add_mail_code_story->getCodeStoryData();
		$set             = [
			"error_count" => $story_code_data->error_count + 1,
			"updated_at"  => time(),
		];
		Gateway_Db_PivotMail_MailAddViaCodeStory::set($add_mail_story_map, $story_code_data->mail, $set);
		$add_mail_code_story = Domain_User_Entity_Security_AddMail_CodeStory::updateStoryData($story_code_data->mail, $story_code_data, $set);
		$story_code_data     = $add_mail_code_story->getCodeStoryData();

		// получаем актуальное кол-во попыток
		$available_attempts = $add_mail_code_story->getAvailableAttempts();

		// если не осталось попыток, отмечаем, что смена номера провалилась
		if ($available_attempts <= 0) {

			$set = [
				"status"     => Domain_User_Entity_Security_AddMail_Story::STATUS_FAIL,
				"updated_at" => time(),
			];

			Gateway_Db_PivotMail_MailAddStory::set($add_mail_story_map, $set);
		}

		// обновляем кеш
		$add_mail_code_story->storeInSessionCache($story_code_data->mail);

		return [$available_attempts, $add_mail_code_story->getNextResend()];
	}
}