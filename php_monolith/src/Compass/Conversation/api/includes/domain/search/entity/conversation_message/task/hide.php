<?php

namespace Compass\Conversation;

/**
 * Задача удаления сообщения из индекса для конкретных пользователей.
 * Запускается при скрытии сообщений пользователями.
 */
class Domain_Search_Entity_ConversationMessage_Task_Hide extends Domain_Search_Entity_Task {

	/** @var int тип задачи */
	public const TASK_TYPE = Domain_Search_Const::TASK_TYPE_CONVERSATION_MESSAGE_HIDE;

	/** @var int сколько задач указанного типа можно брать на индексацию в одну итерацию */
	protected const _PER_ITERATION_LIMIT = 1000;

	/** @var int сколько ошибок допустимо для задачи этого типа */
	protected const _ERROR_LIMIT = 3;

	/** @var int тип сущности сообщения */
	protected const _MESSAGE_ENTITY_TYPE = Domain_Search_Const::TYPE_CONVERSATION_MESSAGE;

	/**
	 * Добавляет задачи в очередь индексации.
	 */
	public static function queueList(array $message_map_list, array $user_id_list):void {

		// убираем сообщения, непригодные для индексации
		// и формируем список задач индексации
		$task_list = array_map(
			static fn(string $message_map) => Struct_Domain_Search_Task::fromDeclaration(
				static::TASK_TYPE, new Struct_Domain_Search_IndexTask_Common($message_map, $user_id_list)
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
		$task_data_list  = [];

		foreach ($task_list as $task) {

			$task_data        = new Struct_Domain_Search_IndexTask_Common(...$task->data);
			$task_data_list[] = $task_data;

			$app_entity_list[] = new Struct_Domain_Search_AppEntity(static::_MESSAGE_ENTITY_TYPE, $task_data->entity_map);
		}

		// получаем search_id для сущностей
		$entity_search_rel_list = Domain_Search_Repository_ProxyCache_EntitySearchId::load($app_entity_list);

		foreach ($task_data_list as $task_data) {

			if (!isset($entity_search_rel_list[$task_data->entity_map])) {

				static::yell("[WARN] search rel для сообщения %s не найдена", $task_data->entity_map);
				continue;
			}

			$search_id = $entity_search_rel_list[$task_data->entity_map];

			static::say("удаляю сообщение для %d пользователей", count($task_data->user_id_list));
			Gateway_Search_Main::deleteForUsers($search_id, $task_data->user_id_list);
		}
	}
}

