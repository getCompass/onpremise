<?php

namespace Compass\Pivot;

/**
 * Действие обновления цвета аватара у пользователя
 */
class Domain_User_Action_UpdateAvatarColor {

	/**
	 * выполняем
	 * @long
	 */
	public static function do(int $user_id, int|false $set_avatar_color_id = false):Struct_Db_PivotUser_User {

		/** начало транзакции */
		Gateway_Db_PivotUser_UserList::beginTransaction($user_id);
		$user_info = Gateway_Db_PivotUser_UserList::getForUpdate($user_id);

		$avatar_color_id = Type_User_Main::getAvatarColorId($user_info->extra);

		// если не передали конкретный цвет, и какой-то цвет аватара уже уставновлен - ничего не делаем
		if ($set_avatar_color_id === false && $avatar_color_id !== 0) {

			Gateway_Db_PivotUser_UserList::rollback($user_id);
			return $user_info;
		}

		// если цвет не передали - генерируем для пользователя
		$set_avatar_color_id = $set_avatar_color_id === false ? \BaseFrame\Domain\User\Avatar::getColorByUserId($user_info->user_id) : $set_avatar_color_id;

		// если вдруг передали неразрешенный цвет - отдаем ошибку
		try {
			\BaseFrame\Domain\User\Avatar::assertAllowedColor($set_avatar_color_id);
		} catch (\BaseFrame\Exception\Domain\ParseFatalException $e) {

			Gateway_Db_PivotUser_UserList::rollback($user_id);
			throw $e;
		}

		// присваиваем пользователю цвета аватара
		$extra = Type_User_Main::setAvatarColorId($user_info->extra, $set_avatar_color_id);

		$user_info->extra = $extra;

		$set = [
			"extra"      => $user_info->extra,
			"updated_at" => time(),
		];

		Gateway_Db_PivotUser_UserList::set($user_id, $set);

		/** начало транзакции */
		Gateway_Db_PivotUser_UserList::commitTransaction($user_id);

		// сбрасываем пивот-кэш для пользователя
		Gateway_Bus_PivotCache::clearUserCacheInfo($user_id);

		// отправляем задачу на обновление данных в компании
		Type_Phphooker_Main::onUserInfoChange($user_id);

		// отправляем WS об изменении профиля пользователя
		$formatted_user_info = Struct_User_Info::createStruct($user_info);
		Gateway_Bus_SenderBalancer::profileEdited($user_id, $formatted_user_info);

		return $user_info;
	}
}