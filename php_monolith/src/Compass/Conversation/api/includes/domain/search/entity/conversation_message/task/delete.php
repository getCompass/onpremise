<?php

namespace Compass\Conversation;

/**
 * Задача удаления сообщения из индекса.
 * Запускается при удалении сообщения для всех пользователей.
 */
class Domain_Search_Entity_ConversationMessage_Task_Delete extends Domain_Search_Entity_Task {

	/** @var int тип задачи */
	public const TASK_TYPE = Domain_Search_Const::TASK_TYPE_CONVERSATION_MESSAGE_DELETE;

	/** @var int сколько задач указанного типа можно брать на индексацию в одну итерацию */
	protected const _PER_ITERATION_LIMIT = 1000;

	/** @var int сколько ошибок допустимо для задачи этого типа */
	protected const _ERROR_LIMIT = 3;

	/** @var int тип сущности сообщения */
	protected const _MESSAGE_ENTITY_TYPE = Domain_Search_Const::TYPE_CONVERSATION_MESSAGE;

	/**
	 * Добавляет задачи в очередь индексации.
	 */
	public static function queueList(array $message_map_list):void {

		// убираем сообщения, непригодные для индексации
		// и формируем список задач индексации
		$task_list = array_map(
			static fn(string $message_map) => Struct_Domain_Search_Task::fromDeclaration(
				static::TASK_TYPE, new Struct_Domain_Search_IndexTask_Entity($message_map)
			),
			$message_map_list
		);

		// вставляем данные
		static::_queue(task_list: $task_list);
	}

	/**
	 * Выполняет указанный список задач.
	 *
	 * @param Struct_Domain_Search_Task[] $task_list
	 */
	public static function execList(array $task_list):void {

		$app_entity_list = [];

		foreach ($task_list as $task) {

			$task_data         = new Struct_Domain_Search_IndexTask_Entity(...$task->data);
			$app_entity_list[] = new Struct_Domain_Search_AppEntity(static::_MESSAGE_ENTITY_TYPE, $task_data->entity_map);
		}

		// получаем search_id для сущностей
		$entity_search_rel_list = Domain_Search_Repository_ProxyCache_EntitySearchId::load($app_entity_list);

		foreach ($entity_search_rel_list as $search_id) {

			static::say("удаляю сообщение для всех");
			Gateway_Search_Main::delete($search_id);
		}
	}
}

