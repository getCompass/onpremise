<?php

namespace Compass\Conversation;

/**
 * Класс-интерфейс для таблицы index_task_queue.
 * Работает с очередью задач индексации данных.
 */
class Gateway_Db_SpaceSearch_Queue_IndexTaskQueue extends Gateway_Db_SpaceSearch_Queue_Abstract {

	protected const _TABLE_KEY = "index_task_queue";
}
