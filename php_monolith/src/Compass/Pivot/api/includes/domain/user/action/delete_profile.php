<?php

namespace Compass\Pivot;

/**
 * Действие для удаления аккаунта пользователя
 */
class Domain_User_Action_DeleteProfile {

	/**
	 * Выполняем.
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws cs_UserNotFound
	 */
	public static function do(int $user_id, string $phone_number):void {

		// помечаем пользователя заблокированным
		try {
			$user_info = Domain_User_Action_DisableProfile::do($user_id);
		} catch (cs_UserAlreadyBlocked) {
			$user_info = Gateway_Bus_PivotCache::getUserInfo($user_id);
		}

		// очищаем все сессии пользователя
		Type_Session_Main::clearAllUserPivotAndCompanySessions($user_id);

		// добавляем в историю действие удаления аккаунта пользователем
		Domain_User_Entity_UserActionComment::addDeleteProfileAction($user_id, $phone_number);

		// сохраняем в историю изменений запись об очистки номера телефона пользователя
		Gateway_Db_PivotHistoryLogs_UserChangePhoneHistory::insert($user_id, $phone_number, "", "", time(), 0);

		// открепляем пользователя от номера телефона
		Domain_User_Entity_Phone::delete($user_id);
		Gateway_Db_PivotPhone_PhoneUniqList::set(Type_Hash_PhoneNumber::makeHash($phone_number), [
			"user_id"           => 0,
			"last_unbinding_at" => time(),
			"updated_at"        => time(),
		]);

		// очищаем аватарку пользователя на стороне pivot
		Domain_User_Action_ClearAvatar::do($user_id);

		// отправляем запрос на удаление файла-аватарки пользователя
		if (!isEmptyString($user_info->avatar_file_map)) {
			Gateway_Socket_PivotFileBalancer::deleteFiles([$user_info->avatar_file_map]);
		}

		// отправляем таск для выполнения оставшихся действий при удалении аккаунта пользователя
		Type_Phphooker_Main::onProfileDelete($user_id);
		Type_Phphooker_Main::sendUserAccountLog($user_id, Type_User_Analytics::DELETED);
	}
}
