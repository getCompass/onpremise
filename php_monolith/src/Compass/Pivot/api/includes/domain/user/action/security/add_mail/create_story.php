<?php

namespace Compass\Pivot;

/**
 *
 */
class Domain_User_Action_Security_AddMail_CreateStory {

	/**
	 * Выполняем начало процесса добавления почты
	 *
	 * @return array
	 * @throws \queryException
	 */
	public static function do(int $user_id, string $session_uniq, string $mail):array {

		$story      = Domain_User_Entity_Security_AddMail_Story::createNewStory($user_id, $session_uniq, $mail);
		$story_data = $story->getStoryData();

		$story_id           = Gateway_Db_PivotMail_MailAddStory::insert($story_data);
		$story              = Domain_User_Entity_Security_AddMail_Story::updateStoryData($mail, $story_data, ["add_mail_story_id" => $story_id]);
		$add_mail_story_map = $story->getStoryMap();

		$story_code      = Domain_User_Entity_Security_AddMail_CodeStory::createNewCodeStory(
			$add_mail_story_map,
			$mail,
			$story_data->stage,
			generateUUID(),
			generateConfirmCode(),
		);
		$story_code_data = $story_code->getCodeStoryData();
		Gateway_Db_PivotMail_MailAddViaCodeStory::insert($story_code_data);

		$story->storeInSessionCache($mail);
		$story_code->storeInSessionCache($mail);

		return [$story, $story_code];
	}
}