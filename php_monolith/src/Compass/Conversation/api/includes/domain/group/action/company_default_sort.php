<?php

namespace Compass\Conversation;

/**
 * Action для сортировки групп в лм в нужном порядке
 */
class Domain_Group_Action_CompanyDefaultSort {

	/**
	 * Сортируем группы в избранном
	 */
	public static function do(int $user_id):void {

		// получаем все группы в избранном у пользователя
		$left_menu_row_list = Type_Conversation_LeftMenu::getLeftMenuFavorites($user_id, 1000);

		// получаем время
		$time = time() + 1;

		// поднимаем updated_at general чату, чтобы он был сверху в избранном
		foreach ($left_menu_row_list as $left_menu_row) {

			if ($left_menu_row["type"] == CONVERSATION_TYPE_GROUP_GENERAL) {

				// поднимаем чат в лм
				// если будет нужно отсортировать другие чаты, там инкремент уже +2, +3 для тех кто должен быть выше
				time_sleep_until($time);
				Domain_User_Action_Conversation_UpdateLeftMenu::do($left_menu_row["user_id"], $left_menu_row["conversation_map"], [
					"updated_at" => $time,
				]);
			}
		}
	}
}
