<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;

/**
 * Действие обновления профиля
 */
class Domain_User_Action_UpdateProfile {

	/**
	 * Выполняем обновление профиля
	 *
	 * @param int          $user_id
	 * @param string|false $name
	 * @param string|false $avatar_file_map
	 * @param string       $client_launch_uuid
	 *
	 * @return array
	 *
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws BusFatalException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 * @throws cs_FileIsNotImage
	 * @long
	 */
	public static function do(int $user_id, string|false $name, string|false $avatar_file_map, string $client_launch_uuid = ""):array {

		Gateway_Db_PivotUser_UserList::beginTransaction($user_id);
		$user_info = Gateway_Db_PivotUser_UserList::getForUpdate($user_id);

		// проверяем, что это первое заполнение профиля
		$is_profile_was_empty = Domain_User_Entity_User::isEmptyProfile($user_info);

		// формируем массив на обновление
		$updated_at            = time();
		$updated               = ["updated_at" => $updated_at];
		$user_info->updated_at = $updated_at;

		if ($name !== false) {

			$updated["full_name"] = $name;
			$user_info->full_name = $name;
		}

		// ставим или удаляем
		if ($avatar_file_map !== false) {

			// если это не изображение
			if (Type_Pack_File::getFileType($avatar_file_map) != FILE_TYPE_IMAGE) {
				throw new cs_FileIsNotImage();
			}

			$updated["avatar_file_map"] = $avatar_file_map;
			$user_info->avatar_file_map = $avatar_file_map;
		}

		Gateway_Db_PivotUser_UserList::set($user_id, $updated);
		Gateway_Db_PivotUser_UserList::commitTransaction($user_id);

		Gateway_Bus_PivotCache::clearUserCacheInfo($user_id);

		$formatted_user_info = Struct_User_Info::createStruct($user_info);

		// отправляем WS
		Gateway_Bus_SenderBalancer::profileEdited($user_id, $formatted_user_info);

		// сохраняем данные, которые были обновлены в профиле пользователя
		unset($updated["updated_at"]);
		Domain_User_Entity_UserActionComment::addProfileDataChangeAction($user_id, $updated);

		Type_Phphooker_Main::onUserInfoChange($user_id, $client_launch_uuid);
		return [$is_profile_was_empty, $formatted_user_info];
	}
}