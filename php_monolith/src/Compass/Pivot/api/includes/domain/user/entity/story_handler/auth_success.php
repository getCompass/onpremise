<?php

namespace Compass\Pivot;

/**
 * Класс для обновление истории при успешной аутентификации
 *
 * Class Domain_User_Entity_StoryHandler_AuthSuccess
 */
class Domain_User_Entity_StoryHandler_AuthSuccess extends Domain_User_Entity_StoryHandler {

	/**
	 * Запись истории
	 *
	 * @throws \parseException
	 * @throws \queryException
	 * @long
	 */
	public static function handle(Struct_User_Auth_Story $story, int $user_id):Struct_User_Auth_Story {

		$time     = time();
		$auth_map = $story->auth_phone->auth_map;
		Gateway_Db_PivotAuth_AuthList::set($auth_map, [
			"user_id"    => $user_id,
			"is_success" => 1,
			"updated_at" => $time,
		]);
		Gateway_Db_PivotAuth_AuthPhoneList::set($auth_map, [
			"is_success" => 1,
			"updated_at" => $time,
		]);
		Gateway_Db_PivotHistoryLogs_UserAuthHistory::insert($auth_map, $user_id, Domain_User_Entity_AuthStory::AUTH_STATUS_SUCCESS, $time, $time);

		// пишем статистику по введенной смс
		Gateway_Bus_CollectorAgent::init()->add("sms_history", [
			"uniq_key"     => $story->auth_phone->sms_id,
			"is_success"   => $story->auth_phone->is_success,
			"resend_count" => $story->auth_phone->resend_count,
			"error_count"  => $story->auth_phone->error_count,
			"created_at"   => $story->auth_phone->created_at,
		]);

		return new Struct_User_Auth_Story(
			new Struct_Db_PivotAuth_Auth(
				$story->auth->auth_uniq,
				$user_id,
				1,
				$story->auth->type,
				$story->auth->created_at,
				$time,
				$story->auth->expires_at,
				$story->auth->ua_hash,
				$story->auth->ip_address
			),
			$story->auth_phone
		);
	}
}