<?php

namespace Compass\Conversation;

/**
 * Задача удаления сообщения из индекса для конкретных пользователей.
 * Запускается при скрытии сообщений пользователями.
 */
class Domain_Search_Entity_ThreadMessage_Task_Hide extends Domain_Search_Entity_ConversationMessage_Task_Hide {

	/** @var int тип задачи */
	public const TASK_TYPE = Domain_Search_Const::TASK_TYPE_THREAD_MESSAGE_HIDE;

	/** @var int сколько задач указанного типа можно брать на индексацию в одну итерацию */
	protected const _PER_ITERATION_LIMIT = 1000;

	/** @var int сколько ошибок допустимо для задачи этого типа */
	protected const _ERROR_LIMIT = 3;

	/** @var int тип сущности сообщения */
	protected const _MESSAGE_ENTITY_TYPE = Domain_Search_Const::TYPE_THREAD_MESSAGE;
}

