<?php

namespace Compass\Pivot;

/**
 * Обновление истории при переотправке
 *
 * Class Domain_User_Entity_StoryHandler_ResendStory
 */
class Domain_User_Entity_StoryHandler_ResendStory extends Domain_User_Entity_StoryHandler {

	/**
	 * Обновляем историю с переотправкой
	 *
	 * @throws cs_IncorrectSaltVersion
	 * @throws \parseException
	 */
	public static function handle(Struct_User_Auth_Story $story, string $sms_code, string $sms_id):Struct_User_Auth_Story {

		// пересоздаем структуру и возвращаем обновленные данные
		$time       = time();
		$auth_phone = new Struct_Db_PivotAuth_AuthPhone(
			$story->auth_phone->auth_map,
			$story->auth_phone->is_success,
			$story->auth_phone->resend_count + 1,
			$story->auth_phone->error_count,
			$story->auth_phone->created_at,
			$time,
			$time + Domain_User_Entity_AuthStory::NEXT_ATTEMPT_AFTER,
			$sms_id,
			Type_Hash_Code::makeHash($sms_code),
			$story->auth_phone->phone_number,
		);

		// обновляем базу
		Gateway_Db_PivotAuth_AuthPhoneList::set($story->auth_phone->auth_map, [
			"resend_count"   => "resend_count + 1",
			"updated_at"     => $time,
			"next_resend_at" => $auth_phone->next_resend_at,
			"sms_code_hash"  => Type_Hash_Code::makeHash($sms_code),
			"sms_id"         => $sms_id,
		]);

		return new Struct_User_Auth_Story(
			$story->auth,
			$auth_phone
		);
	}
}