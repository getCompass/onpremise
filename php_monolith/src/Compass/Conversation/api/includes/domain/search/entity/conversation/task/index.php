<?php declare(strict_types=1);

namespace Compass\Conversation;

/**
 * Задача первичной индексации для диалога.
 */
class Domain_Search_Entity_Conversation_Task_Index extends Domain_Search_Entity_Task {

	/**
	 * Подготавливает диалоги для дальнейшей работы в поиске.
	 */
	public static function queueList(array $conversation_map_list):void {

		// формируем список сущностей
		$entity_list = array_map(
			static fn(string $el) => new Struct_Domain_Search_AppEntity(Domain_Search_Const::TYPE_CONVERSATION, $el),
			$conversation_map_list
		);

		// вставляем данные
		static::_queue($entity_list);
	}

	/**
	 * Выполняет указанный список задач.
	 * Для диалогов на текущий момент нет задачи индексации.
	 *
	 * @param Struct_Domain_Search_Task[] $task_list
	 */
	public static function execList(array $task_list):void {

	}
}
