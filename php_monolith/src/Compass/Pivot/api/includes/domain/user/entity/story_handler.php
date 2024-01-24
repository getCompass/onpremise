<?php

namespace Compass\Pivot;

/**
 * Запись историй аутентификации
 *
 * Class Domain_User_Entity_StoryHandler
 */
class Domain_User_Entity_StoryHandler {

	/**
	 * Запись новых данных в базу
	 *
	 * @throws cs_IncorrectSaltVersion
	 * @throws \queryException
	 * @throws \returnException
	 */
	protected static function _storeNewAuthStory(
		string $auth_uniq,
		string $auth_map,
		string $phone_number,
		string $sms_code,
		string $sms_id,
		int    $user_id,
		int    $type,
		int    $shard_id,
		int    $time,
		int    $expired_at,
		int    $next_attempt
	):Struct_User_Auth_Story {

		Gateway_Db_PivotAuth_Main::beginTransaction($shard_id);

		// вставляем запись о аутентификации пользователя
		$auth = new Struct_Db_PivotAuth_Auth(
			$auth_uniq, $user_id, 0, $type, $time, $time, $expired_at, Type_Hash_UserAgent::makeHash(getUa()), getIp()
		);
		Gateway_Db_PivotAuth_AuthList::insert($auth);

		// вставляем запись о телефоне и смс пользователя
		$auth_phone = new Struct_Db_PivotAuth_AuthPhone(
			$auth_map, 0, 0, 0, $time, $time, $next_attempt, $sms_id, Type_Hash_Code::makeHash($sms_code), $phone_number
		);
		Gateway_Db_PivotAuth_AuthPhoneList::insert($auth_phone);

		Gateway_Db_PivotAuth_Main::commitTransaction($shard_id);

		return new Struct_User_Auth_Story($auth, $auth_phone);
	}
}