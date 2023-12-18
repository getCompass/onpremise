<?php

declare(strict_types = 1);

namespace Compass\Thread;

/**
 * Action для подписки пользователей к треду
 */
class Domain_Thread_Action_Follower_Follow {

	/**
	 * выполняем действие
	 */
	public static function do(array $user_id_list, string $thread_map, array $parent_rel):array {

		// получаем запись из follower_list
		$follower_row = Type_Thread_Followers::get($thread_map);

		$need_follow_user_id_list = [];
		foreach ($user_id_list as $user_id) {

			// если пользователь  уже участник треда
			if (Type_Thread_Followers::isFollowUser($user_id, $follower_row)) {
				continue;
			}
			$need_follow_user_id_list[] = $user_id;
		}

		if (count($need_follow_user_id_list) < 1) {
			return $follower_row;
		}

		// подписываем пользователя на тред
		$follower_row = Type_Thread_Followers::doFollowUserList($need_follow_user_id_list, $thread_map);

		// создаем записи
		Type_Thread_Menu::setFollowUserList($need_follow_user_id_list, $thread_map, $parent_rel);

		return $follower_row;
	}
}