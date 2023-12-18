<?php

namespace Compass\Conversation;

/**
 * Обработчик задач индексации.
 */
class Domain_Search_Entity_TaskHandler {

	/** @var Domain_Search_Entity_Task[] */
	protected const _TASK_HANDLER_LIST = [

		// задачи пространств
		Domain_Search_Entity_Space_Task_InitReindex::class,
		Domain_Search_Entity_Space_Task_ReindexConversations::class,

		// задачи диалогов
		Domain_Search_Entity_Conversation_Task_Index::class,
		Domain_Search_Entity_Conversation_Task_Reindex::class,
		Domain_Search_Entity_Conversation_Task_Clear::class,
		Domain_Search_Entity_Conversation_Task_Purge::class,

		// задачи тредов
		Domain_Search_Entity_Thread_Task_Reindex::class,

		// задачи для сообщений
		Domain_Search_Entity_ConversationMessage_Task_Index::class,
		Domain_Search_Entity_ConversationMessage_Task_Reindex::class,
		Domain_Search_Entity_ConversationMessage_Task_Hide::class,
		Domain_Search_Entity_ConversationMessage_Task_Delete::class,

		// задачи комментариев
		Domain_Search_Entity_ThreadMessage_Task_Index::class,
		Domain_Search_Entity_ThreadMessage_Task_Reindex::class,
		Domain_Search_Entity_ThreadMessage_Task_Hide::class,
		Domain_Search_Entity_ThreadMessage_Task_Delete::class,

		// задачи превью
		Domain_Search_Entity_Preview_Task_AttachToConversationMessage::class,
		Domain_Search_Entity_Preview_Task_AttachToThreadMessage::class,

		// задачи файлов
		Domain_Search_Entity_File_Task_Index::class,
		Domain_Search_Entity_File_Task_Reindex::class,
		Domain_Search_Entity_File_Task_AttachToConversationMessage::class,
		Domain_Search_Entity_File_Task_AttachToThreadMessage::class,
	];

	/**
	 * Возвращает число ошибок, которое может быть совершено при работе с этой задачей.
	 * Если задача ставит другие задачи, то лимит должен быть равен 1.
	 *
	 * @throws Domain_Search_Exception_IndexationUnallowable
	 */
	public static function getErrorLimit(int $task_type):int {

		foreach (static::_TASK_HANDLER_LIST as $task_class) {

			if ($task_class::TASK_TYPE !== $task_type) {
				continue;
			}

			return $task_class::getErrorLimit();
		}

		throw new Domain_Search_Exception_IndexationUnallowable("passed unknown task type $task_type");
	}

	/**
	 * Разбивает однотипные подряд идущие задачи из базы на итерации.
	 * @throws Domain_Search_Exception_IndexationUnallowable
	 */
	public static function splitIntoChunks(int $task_type, array $full_task_list):array {

		foreach (static::_TASK_HANDLER_LIST as $task_class) {

			if ($task_class::TASK_TYPE !== $task_type) {
				continue;
			}

			return $task_class::splitIntoChunks($full_task_list);
		}

		throw new Domain_Search_Exception_IndexationUnallowable("passed unknown task type $task_type");
	}

	/**
	 * Выполняет пачку однотипных задач.
	 *
	 * @param Struct_Domain_Search_Task[] $current_chunk
	 * @throws Domain_Search_Exception_IndexationUnallowable
	 */
	public static function handleList(int $task_type, array $current_chunk):void {

		foreach (static::_TASK_HANDLER_LIST as $task_class) {

			if ($task_class::TASK_TYPE !== $task_type) {
				continue;
			}

			$task_class::say("---");
			$task_class::say("pick %d tasks in work", count($current_chunk));
			$task_class::execList($current_chunk);
			$task_class::say("done with %d tasks", count($current_chunk));

			// записываем метрики
			Domain_Search_Repository_ProxyCache::instance()->writeMetrics();
			Domain_Search_Helper_Stemmer::writeMetrics();
			Domain_Search_Helper_Highlight::writeMetrics();
			Domain_Search_Helper_FormattingCleaner::writeMetrics();

			return;
		}

		throw new Domain_Search_Exception_IndexationUnallowable("passed unknown task type $task_type");
	}
}