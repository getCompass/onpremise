<?php

namespace Compass\Pivot;

/**
 * Запись истории аутентификации (логина)
 *
 * Class Domain_User_Entity_StoryHandler_LoginStory
 */
class Domain_User_Entity_StoryHandler_LoginStory extends Domain_User_Entity_StoryHandler {

	/**
	 * добавляем в историю аутентификацию
	 *
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function handle(int $user_id, string $phone_number, string $sms_code, string $sms_id):Struct_User_Auth_Story {

		// данные для новой записи
		$auth_uniq    = generateUUID();
		$time         = time();
		$next_attempt = $time + Domain_User_Entity_AuthStory::NEXT_ATTEMPT_AFTER;
		$expired_at   = $time + Domain_User_Entity_AuthStory::EXPIRE_AT;
		$type         = Domain_User_Entity_AuthStory::AUTH_STORY_TYPE_LOGIN;

		// данные для шардинга
		$shard_id = Type_Pack_Auth::getShardIdByTime($time);
		$table_id = Type_Pack_Auth::getTableIdByTime($time);
		$auth_map = Type_Pack_Auth::doPack($auth_uniq, $shard_id, $table_id, $time);

		return self::_storeNewAuthStory(
			$auth_uniq,
			$auth_map,
			$phone_number,
			$sms_code,
			$sms_id,
			$user_id,
			$type,
			$shard_id,
			$time,
			$expired_at,
			$next_attempt
		);
	}
}