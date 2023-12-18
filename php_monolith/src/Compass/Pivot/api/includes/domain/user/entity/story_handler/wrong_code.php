<?php

namespace Compass\Pivot;

/**
 * Класс для записи истории ошибок при подтверждении
 *
 * Class Domain_User_Entity_StoryHandler_WrongCode
 */
class Domain_User_Entity_StoryHandler_WrongCode {

	/**
	 * Запись истории
	 *
	 * @throws \parseException
	 */
	public static function handle(Struct_User_Auth_Story $story):Struct_User_Auth_Story {

		$time = time();
		Gateway_Db_PivotAuth_AuthPhoneList::set($story->auth_phone->auth_map, [
			"error_count" => "error_count + 1",
			"updated_at"  => $time,
		]);

		return new Struct_User_Auth_Story(
			$story->auth,
			new Struct_Db_PivotAuth_AuthPhone(
				$story->auth_phone->auth_map,
				$story->auth_phone->is_success,
				$story->auth_phone->resend_count,
				$story->auth_phone->error_count + 1,
				$story->auth_phone->created_at,
				$time,
				$story->auth_phone->next_resend_at,
				$story->auth_phone->sms_id,
				$story->auth_phone->sms_code_hash,
				$story->auth_phone->phone_number,
			)
		);
	}
}