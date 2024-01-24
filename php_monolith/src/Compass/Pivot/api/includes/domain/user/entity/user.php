<?php

namespace Compass\Pivot;

/**
 * класс для работы с данными пользователя
 */
class Domain_User_Entity_User {

	/**
	 * Получить минимальные данные о пользователе
	 *
	 * @throws \busException
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \userAccessException
	 */
	public static function getMinimalInfo(int $user_id):array {

		$user_data = Gateway_Bus_PivotCache::getUserInfo($user_id);

		$info = [
			"full_name" => $user_data->full_name,
		];
		if ($user_data->avatar_file_map !== "") {

			$info["inviter_user"]["avatar"] = [
				"file_map" => $user_data->avatar_file_map,
			];
		}
		return $info;
	}

	/**
	 * Выбросить команду, если пустой профиль у пользователя
	 *
	 * @throws \busException
	 * @throws cs_AnswerCommand
	 * @throws cs_UserNotFound
	 * @throws \parseException
	 * @throws \userAccessException
	 */
	public static function throwCommandIfEmptyProfile(int $user_id):void {

		// получаем информацию о пользователе
		$user_info = Gateway_Bus_PivotCache::getUserInfo($user_id);

		// если профиль пустой, то просим его заполнить
		if (self::isEmptyProfile($user_info)) {
			throw new cs_AnswerCommand("need_fill_profile", []);
		}
	}

	/**
	 * Проверяем, что профайл не заполнен
	 *
	 * @return bool
	 */
	public static function isEmptyProfile(Struct_Db_PivotUser_User $user_info):bool {

		return mb_strlen($user_info->full_name) < 1;
	}
}