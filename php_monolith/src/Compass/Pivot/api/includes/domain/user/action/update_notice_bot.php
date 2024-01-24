<?php

namespace Compass\Pivot;

/**
 * метод устанавливает дефолтный аватар боту объявлений и имя
 */
class Domain_User_Action_UpdateNoticeBot {

	/**
	 * выполняем обновление аватара и имени у бота объявлений
	 *
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	public static function do():Struct_User_Info {

		$user_id         = AUTH_BOT_USER_ID;
		$default_file    = Gateway_Db_PivotSystem_DefaultFileList::get("notice_bot_avatar");
		$bot_info_list   = fromJson(file_get_contents(PIVOT_MODULE_ROOT . "sh/start/bot_info_list.json"));
		$full_name       = $bot_info_list[0]["name"];
		$avatar_file_map = Type_Pack_File::doDecrypt($default_file->file_key);

		// формируем массив на обновление
		$updated = [
			"updated_at"      => time(),
			"full_name"       => $full_name,
			"avatar_file_map" => $avatar_file_map,
		];

		// обновляем юзера и скидываем кеш
		Gateway_Db_PivotUser_UserList::set($user_id, $updated);
		Gateway_Bus_PivotCache::clearUserCacheInfo($user_id);

		$user_info = Gateway_Db_PivotUser_UserList::getOne($user_id);

		$formatted_user_info = Struct_User_Info::createStruct($user_info);

		// отправляем WS
		Gateway_Bus_SenderBalancer::profileEdited($user_id, $formatted_user_info);

		Type_Phphooker_Main::onUserInfoChange($user_id);
		return $formatted_user_info;
	}
}
