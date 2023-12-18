<?php

namespace Compass\Conversation;

/**
 * Класс-интерфейс для таблицы entity_preparation_queue.
 * Работает с очередью задач подготовки данных для индексации.
 */
class Gateway_Db_SpaceSearch_Queue_EntityPreparationQueue extends Gateway_Db_SpaceSearch_Queue_Abstract {

	protected const _TABLE_KEY = "entity_preparation_task_queue";
}
