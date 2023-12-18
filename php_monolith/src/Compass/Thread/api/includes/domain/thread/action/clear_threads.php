<?php

namespace Compass\Thread;

/**
 * Отпишем пользователя от тредов
 */
class Domain_Thread_Action_ClearThreads {

	/**
	 * Отпишем пользователя от тредов
	 *
	 **/
	public static function run(int $user_id, int $limit, int $offset):bool {

		$thread_map_list = Domain_Thread_Entity_ThreadsUser::getThreadsByUserId($user_id, $limit, $offset);

		// отписываеся от каждого треда
		foreach ($thread_map_list as $v) {
			Domain_Thread_Action_Follower_Unfollow::do($user_id, $v, true);
		}

		// если тредов меньше чем лимит - значит это были последние
		if (count($thread_map_list) < $limit) {
			return true;
		}
		return false;
	}
}
