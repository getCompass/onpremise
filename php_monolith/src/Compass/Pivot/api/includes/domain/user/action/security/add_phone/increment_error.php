<?php

namespace Compass\Pivot;

/**
 * Увеличиваем ошибку при подтверждении
 */
class Domain_User_Action_Security_AddPhone_IncrementError {

	/**
	 * Выполняем инкремент ошибки
	 *
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 */
	public static function do(Domain_User_Entity_Security_AddPhone_SmsStory $sms_story, Domain_User_Entity_Security_AddPhone_Story $story):int {

		// увеличиваем счетчик ошибок
		$sms_story_data = $sms_story->getSmsStoryData();
		Gateway_Db_PivotPhone_PhoneAddViaSmsStory::set(
			$story->getStoryMap(),
			$sms_story_data->phone_number,
			[
				"error_count" => $sms_story_data->error_count + 1,
				"updated_at"  => time(),
			],
		);

		// получаем актуальное кол-во попыток
		$available_attempts = $sms_story->getAvailableAttempts() - 1;

		// если не осталось попыток, отмечаем, что смена номера провалилась
		if ($available_attempts <= 0) {

			Gateway_Db_PivotPhone_PhoneAddStory::set(
				$story->getStoryMap(),
				[
					"status"     => Domain_User_Entity_Security_AddPhone_Story::STATUS_FAIL,
					"updated_at" => time(),
				],
			);
		}

		return $available_attempts;
	}
}