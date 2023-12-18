<?php

namespace Compass\Conversation;

/**
 * Получаем список тредов батчингом
 */
class Domain_Conversation_Feed_Action_GetBatchingThreads {

	/**
	 * выполняем
	 *
	 * @param int   $user_id
	 * @param array $block_list_by_conversation_map
	 *
	 * @return array
	 * @throws \parseException
	 */
	public static function run(int $user_id, array $block_list_by_conversation_map, array $dynamic_list, array $meta_list):array {

		$thread_rel_list = Gateway_Db_CompanyConversation_MessageThreadRel::getSpecifiedList($block_list_by_conversation_map);

		$thread_map_list_by_conversation_map = [];
		$need_thread_map_list                = [];
		foreach ($thread_rel_list as $thread) {

			$need_thread_map_list[] = $thread->thread_map;

			$thread_map_list_by_conversation_map[$thread->conversation_map][] = $thread->thread_map;
		}

		[$thread_meta_list, $thread_menu_list] = Gateway_Socket_Thread::getThreadListForBatchingFeed($user_id, $need_thread_map_list, $dynamic_list, $meta_list);

		$thread_meta_list_by_conversation_map = [];
		$thread_menu_list_by_conversation_map = [];

		foreach ($block_list_by_conversation_map as $conversation_map => $_) {
			
			$thread_map_list = $thread_map_list_by_conversation_map[$conversation_map] ?? [];

			$thread_meta_list_by_conversation_map[$conversation_map] = array_values(array_filter($thread_meta_list,
				static fn(array $thread_meta) => in_array($thread_meta["thread_map"], $thread_map_list)
			));

			$thread_menu_list_by_conversation_map[$conversation_map] = array_values(array_filter($thread_menu_list,
				static fn(array $thread_menu) => in_array($thread_menu["thread_map"], $thread_map_list)
			));
		}

		return [$thread_meta_list_by_conversation_map, $thread_menu_list_by_conversation_map];
	}
}
