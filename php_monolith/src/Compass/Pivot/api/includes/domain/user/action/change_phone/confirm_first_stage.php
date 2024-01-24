<?php

namespace Compass\Pivot;

/**
 * Подтверждаем первый этап смены телефона и переходим на следующий
 */
class Domain_User_Action_ChangePhone_ConfirmFirstStage {

	/**
	 * Выполняем процесс подтверждения первого этапа
	 *
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 */
	public static function do(Domain_User_Entity_ChangePhone_SmsStory $sms_story, Domain_User_Entity_ChangePhone_Story $story):array {

		// завершаем подтверждение смс
		$sms_story_data       = $sms_story->getSmsStoryData();
		$sms_story_update_set = [
			"status"     => Domain_User_Entity_ChangePhone_SmsStory::STATUS_SUCCESS,
			"updated_at" => time(),
		];
		Gateway_Db_PivotPhone_PhoneChangeViaSmsStory::set(
			$story->getStoryMap(),
			$sms_story_data->phone_number,
			$sms_story_update_set,
		);
		$updated_sms_story = Domain_User_Entity_ChangePhone_SmsStory::createFromAnotherSmsStoryData($sms_story_data, $sms_story_update_set);

		$story_update_set = [
			"stage"      => Domain_User_Entity_ChangePhone_Story::STAGE_SECOND,
			"updated_at" => time(),
		];
		Gateway_Db_PivotPhone_PhoneChangeStory::set(
			$story->getStoryMap(),
			$story_update_set,
		);
		$updated_story = Domain_User_Entity_ChangePhone_Story::createFromAnotherStoryData($story->getStoryData(), $story_update_set);

		return [$updated_story, $updated_sms_story];
	}
}