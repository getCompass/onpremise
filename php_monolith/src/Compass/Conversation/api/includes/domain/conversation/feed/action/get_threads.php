<?php

namespace Compass\Conversation;

/**
 * Получаем список тредов
 */
class Domain_Conversation_Feed_Action_GetThreads {

	/**
	 * @param int    $user_id
	 * @param string $conversation_map
	 * @param array  $block_id_list
	 *
	 * @return array
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function run(int $user_id, string $conversation_map, array $block_id_list):array {

		$thread_rel_list = Gateway_Db_CompanyConversation_MessageThreadRel::getThreadListByBlockList($conversation_map, $block_id_list);

		$need_thread_map_list = [];
		foreach ($thread_rel_list as $thread) {
			$need_thread_map_list[] = $thread->thread_map;
		}

		// получаем информацию о запрашиваемых тредах
		[$thread_meta_list, $thread_menu_list] = Gateway_Socket_Thread::getThreadListForFeed($user_id, $need_thread_map_list);

		// пробежимся по thread-meta каждого треда и соберем уникальный список всех отправителей для action users:
		$thread_action_users = [];
		foreach ($thread_meta_list as $thread_meta) {

			// если есть отправители, то собираем их
			if (count($thread_meta["sender_user_list"]) > 0) {
				$thread_action_users = array_merge($thread_action_users, $thread_meta["sender_user_list"]);
			}
		}

		// унифицируем массив
		$thread_action_users = array_unique($thread_action_users);

		return [$thread_meta_list, $thread_menu_list, $thread_action_users];
	}
}
