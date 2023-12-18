<?php

declare(strict_types = 1);

namespace Compass\Thread;

/**
 * Action для фильтрации итемов меню тредов
 */
class Domain_Thread_Entity_ThreadMenu {

	// получаем все треды
	public static function getMenu(int $user_id, int $count, int $offset):array {

		$has_next = 0;

		// получаем список непрочитанных тредов на которые пользователь подписан и которые не скрыты
		$thread_menu_list = Type_Thread_Menu::getMenu($user_id, $count + 1, $offset);
		if (count($thread_menu_list) > $count) {

			$has_next         = 1;
			$thread_menu_list = array_slice($thread_menu_list, 0, $count);
		}

		// проверяем доступность полученных итемов меню тредов для пользователя
		return [self::_doFilterThreadMenuList($user_id, $thread_menu_list), $has_next];
	}

	// получаем непрочитанные треды
	public static function getUnread(int $user_id, int $count, int $offset):array {

		$has_next = 0;

		// получаем список непрочитанных тредов на которые пользователь подписан и которые не скрыты
		$thread_menu_list = Type_Thread_Menu::getUnreadMenu($user_id, $count + 1, $offset);
		if (count($thread_menu_list) > $count) {

			$has_next         = 1;
			$thread_menu_list = array_slice($thread_menu_list, 0, $count);
		}

		// проверяем доступность полученных итемов меню тредов для пользователя
		return [self::_doFilterThreadMenuList($user_id, $thread_menu_list), $has_next];
	}

	/**
	 * получаем треды по флагу
	 *
	 * @throws busException
	 * @throws parseException
	 */
	public static function getFavorite(int $user_id, int $count, int $offset, int $favorite_filter):array {

		$has_next = 0;

		// получаем список непрочитанных тредов на которые пользователь подписан и которые не скрыты
		$thread_menu_list = Type_Thread_Menu::getFavoriteMenu($user_id, $count + 1, $offset, $favorite_filter);
		if (count($thread_menu_list) > $count) {

			$has_next         = 1;
			$thread_menu_list = array_slice($thread_menu_list, 0, $count);
		}

		// проверяем доступность полученных итемов меню тредов для пользователя
		return [self::_doFilterThreadMenuList($user_id, $thread_menu_list), $has_next];
	}

	/**
	 * Все заяки найма и увольнения - скрываем
	 *
	 * @param int   $user_id
	 * @param array $thread_menu_list
	 *
	 * @return array
	 */
	protected static function _doFilterThreadMenuList(int $user_id, array $thread_menu_list):array {

		// убираем все треды из чата наймы и увольнения
		[$filtered_thread_menu_list, $not_allowed_thread_map_list] = Domain_Thread_Entity_LegacyTypes::filterThreadMenuFromJoinLegacy($thread_menu_list);

		// отписываем пользователя от тредов из чата "Наймы и увольнения"
		Type_Phphooker_Main::doUnfollowThreadList($not_allowed_thread_map_list, $user_id);

		// возвращаем уже правильный доступный список
		return array_values($filtered_thread_menu_list);
	}
}
