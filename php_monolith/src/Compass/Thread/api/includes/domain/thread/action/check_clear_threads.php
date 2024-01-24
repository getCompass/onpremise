<?php

namespace Compass\Thread;

/**
 * Проверим что пользователя действительно удалили
 */
class Domain_Thread_Action_CheckClearThreads {

	/**
	 * Проверим что пользователя действительно удалили
	 *
	 **/
	public static function run(int $user_id, int $limit, int $offset):bool {

		$unfollow_thread_map_list = Domain_Thread_Entity_ThreadsUser::getThreadsByUserId($user_id, $limit, $offset);

		// если тредов меньше чем лимит - значит это были последние
		if (count($unfollow_thread_map_list) < $limit && count($unfollow_thread_map_list) == 0) {
			return true;
		}

		return false;
	}
}
