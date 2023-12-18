<?php

namespace Compass\Thread;

/**
 * Убираем тред из избранного
 */
class Domain_Thread_Action_RemoveFromFavorite {

	/**
	 * Добавляем тред в избранное
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function do(int $user_id, string $thread_map):void {

		// получаем запись из thread_menu
		Gateway_Db_CompanyThread_Main::beginTransaction();
		$thread_menu_row = Gateway_Db_CompanyThread_UserThreadMenu::getForUpdate($user_id, $thread_map);

		// если треда не оказалось в левом меню, то просто выходим
		if (!isset($thread_menu_row["user_id"])) {

			Gateway_Db_CompanyThread_Main::rollback();
			return;
		}

		// если тред уже не в избранном, то просто выходим
		if ($thread_menu_row["is_favorite"] == 0) {

			Gateway_Db_CompanyThread_Main::rollback();
			return;
		}

		$set = [
			"is_favorite" => 0,
			"updated_at"  => time(),
		];
		Gateway_Db_CompanyThread_UserThreadMenu::set($user_id, $thread_map, $set);
		Gateway_Db_CompanyThread_Main::commitTransaction();

		// отправляем ws событие об удалении треда из избранных
		Gateway_Bus_Sender::threadIsFavoriteChanged($user_id, $thread_map, false);
	}
}