<?php declare(strict_types=1);

namespace Compass\Conversation;

/**
 * Задача очистки диалога для пользователей.
 * Вызывается при очистке диалога пользователем для себя.
 */
class Domain_Search_Entity_Conversation_Task_Clear extends Domain_Search_Entity_Task {

	/** @var int тип задачи */
	public const TASK_TYPE = Domain_Search_Const::TASK_TYPE_CONVERSATION_CLEAR;

	/** @var int сколько ошибок допустимо для задачи этого типа */
	protected const _ERROR_LIMIT = 3;

	/**
	 * Добавляет задачу очистки диалога для списка пользователей
	 */
	public static function queue(string $conversation_map, array $user_id_list):void {

		// добавляем задачи в список
		$task_data   = new Struct_Domain_Search_IndexTask_Common($conversation_map, $user_id_list);
		$task_list[] = Struct_Domain_Search_Task::fromDeclaration(static::TASK_TYPE, $task_data);

		// поставил задачу переиндксации диалога для пользователей
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

			$app_entity_list[] = new Struct_Domain_Search_AppEntity(Domain_Search_Const::TYPE_CONVERSATION, $task_data->entity_map);
		}

		// получаем search_id для сущностей
		$entity_search_rel_list = Domain_Search_Repository_ProxyCache_EntitySearchId::load($app_entity_list);

		foreach ($task_data_list as $task_data) {

			if (!isset($entity_search_rel_list[$task_data->entity_map])) {

				static::yell("[WARN] search rel для диалога диалога %s не найдена, пропускаю", $task_data->entity_map);
				continue;
			}

			$search_id = $entity_search_rel_list[$task_data->entity_map];
			Gateway_Search_Main::deleteByParentForUsers($search_id, $task_data->user_id_list);
		}
	}
}

