<?php

namespace Compass\Pivot;

/**
 * метод устанавливает дефолтный аватар боту Напоминаний
 */
class Domain_User_Action_UpdateRemindBot {

	/**
	 * выполняем обновление аватара у бота Напоминаний
	 *
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	public static function do():void {

		$user_id         = REMIND_BOT_USER_ID;
		$default_file    = Gateway_Db_PivotSystem_DefaultFileList::get("remind_bot_avatar");
		$avatar_file_map = Type_Pack_File::doDecrypt($default_file->file_key);

		// формируем массив на обновление
		$updated = [
			"updated_at"      => time(),
			"avatar_file_map" => $avatar_file_map,
		];

		// обновляем бота и скидываем кеш
		Gateway_Db_PivotUser_UserList::set($user_id, $updated);
		Gateway_Bus_PivotCache::clearUserCacheInfo($user_id);

		// обновляем данные в компаниях
		Type_Phphooker_Main::onUserInfoChange($user_id);
	}
}
