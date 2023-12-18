<?php

namespace Compass\Conversation;

/**
 * Пытается выполнить полную переиндексацию пространства.
 * Нужно для пространств, находившихся в гибернации.
 */
class Domain_Search_Action_TryFullReindex {

	/**
	 * Проверяет необходимость провести полную индексацию.
	 * Индексацию нужно делать, если очеред задач пустая и нет ранее созданных связей сущность-поиск.
	 */
	public static function run():void {

		// получаем число связей сущность-поиск
		$entity_search_rel_count = Gateway_Db_SpaceSearch_EntitySearchIdRel::count();

		// получаем текущую длину очереди задач
		$queue_length = Gateway_Db_SpaceSearch_Queue_EntityPreparationQueue::count();
		$queue_length += Gateway_Db_SpaceSearch_Queue_IndexTaskQueue::count();

		// если есть связи или задачи, то не запускаем индексацию
		if ($entity_search_rel_count !== 0 || $queue_length !== 0) {
			return;
		}

		Domain_Search_Entity_Space_Task_InitReindex::queue();
	}
}
