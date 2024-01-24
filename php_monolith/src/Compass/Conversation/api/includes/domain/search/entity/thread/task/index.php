<?php declare(strict_types=1);

namespace Compass\Conversation;

/**
 * Класс индексатор для тредов.
 */
class Domain_Search_Entity_Thread_Task_Index extends Domain_Search_Entity_Task {

	/**
	 * Подготавливает треды для дальнейшей работы в поиске.
	 */
	public static function queueList(array $thread_map_list):void {

		// формируем список сущностей
		$entity_list = array_map(
			static fn(string $el) => new Struct_Domain_Search_AppEntity(Domain_Search_Const::TYPE_THREAD, $el),
			$thread_map_list
		);

		// вставляем данные
		static::_queue($entity_list);
	}

	/**
	 * Выполняет указанный список задач.
	 * Для тредов на текущий момент нет задачи индексации.
	 *
	 * @param Struct_Domain_Search_Task[] $task_list
	 */
	public static function execList(array $task_list):void {

	}
}
