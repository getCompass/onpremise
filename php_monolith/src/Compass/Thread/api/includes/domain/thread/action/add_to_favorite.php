<?php

namespace Compass\Thread;
/**
 * Добавляем тред в избранное
 */
class Domain_Thread_Action_AddToFavorite {

	/**
	 * Добавляем тред в избранное
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function do(int $user_id, string $thread_map):void {

		// получаем запись из thread_menu
		Gateway_Db_CompanyThread_Main::beginTransaction();
		$thread_menu_row = Gateway_Db_CompanyThread_UserThreadMenu::getForUpdate($user_id, $thread_map);

		if (!isset($thread_menu_row["user_id"])) {

			Gateway_Db_CompanyThread_Main::rollback();
			throw new \cs_RowIsEmpty("not found thread menu for user");
		}

		// если уже в избранном, то просто выходим
		if ($thread_menu_row["is_favorite"] == 1) {

			Gateway_Db_CompanyThread_Main::rollback();
			return;
		}

		$set = [
			"is_favorite" => 1,
			"updated_at"  => time(),
		];
		Gateway_Db_CompanyThread_UserThreadMenu::set($user_id, $thread_map, $set);
		Gateway_Db_CompanyThread_Main::commitTransaction();

		// отправляем ws событие о добавлении треда в избранное
		Gateway_Bus_Sender::threadIsFavoriteChanged($user_id, $thread_map, true);
	}
}