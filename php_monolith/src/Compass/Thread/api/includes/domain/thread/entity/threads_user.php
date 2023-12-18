<?php

namespace Compass\Thread;

/**
 * Класс для работы с тредами пользователя
 */
class Domain_Thread_Entity_ThreadsUser {

	/**
	 * Получим все диалоги пользователя
	 *
	 * @throws \busException
	 * @throws \returnException
	 */
	public static function getThreadsByUserId(int $user_id, int $limit = 500, int $offset = 0):array {

		// получаем список тредов, чтобы отписать от них пользователя
		$unfollow_thread_map_list = [];

		// получаем меню тредов, что еще не скрыты для пользователя
		$thread_menu_list = Type_Thread_Menu::getMenu($user_id, $limit, $offset);
		$thread_map_list  = array_column($thread_menu_list, "thread_map");

		// собираем в единый ответ
		$unfollow_thread_map_list = array_merge($unfollow_thread_map_list, $thread_map_list);

		return $unfollow_thread_map_list;
	}
}
