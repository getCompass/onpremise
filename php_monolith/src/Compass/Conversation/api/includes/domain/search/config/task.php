<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс, описывающий настройки ограничений для задач.
 */
class Domain_Search_Config_Task {

	/**
	 * Маппинг: тип задачи — ключ лимита в конфиге для максимальной сложности за один вызов воркера задачи.
	 *
	 * Т.е. воркер на один вызов не возьмет задач суммарной сложностью больше чем
	 * _PER_EXECUTION_COMPLEXITY_LIMIT/(_MAX_TASK_COMPLEXITY * N_ЗАДАЧ) + _MAX_TASK_COMPLEXITY
	 */
	protected const _PER_EXECUTION_COMPLEXITY_LIMIT = [

		// задачи индексации
		Domain_Search_Entity_ConversationMessage_Task_Index::class           => "per_message_task_execution_complexity_limit",
		Domain_Search_Entity_ThreadMessage_Task_Index::class                 => "per_message_task_execution_complexity_limit",
		Domain_Search_Entity_File_Task_AttachToConversationMessage::class    => "per_file_task_execution_complexity_limit",
		Domain_Search_Entity_File_Task_AttachToThreadMessage::class          => "per_file_task_execution_complexity_limit",
		Domain_Search_Entity_Preview_Task_AttachToConversationMessage::class => "per_preview_task_execution_complexity_limit",
		Domain_Search_Entity_Preview_Task_AttachToThreadMessage::class       => "per_preview_task_execution_complexity_limit",

		// задачи переиндексации
		Domain_Search_Entity_ConversationMessage_Task_Reindex::class         => "per_message_task_execution_complexity_limit",
		Domain_Search_Entity_ThreadMessage_Task_Reindex::class               => "per_message_task_execution_complexity_limit",
		Domain_Search_Entity_File_Task_Reindex::class                        => "per_file_task_execution_complexity_limit",
	];

	/**
	 * Маппинг: тип задачи — ключ лимита в конфиге, для максимальной сложности одной задачи индексации.
	 *
	 * Сумма сложности задач в пачке не должна превышать соответствующее
	 * значение из _PER_EXECUTION_COMPLEXITY_LIMIT более чем на сложность одной задачи.
	 */
	protected const _MAX_TASK_COMPLEXITY = [
		Domain_Search_Entity_ConversationMessage_Task_Index::class           => "max_message_task_complexity",
		Domain_Search_Entity_ThreadMessage_Task_Index::class                 => "max_message_task_complexity",
		Domain_Search_Entity_File_Task_AttachToConversationMessage::class    => "max_file_task_complexity",
		Domain_Search_Entity_File_Task_AttachToThreadMessage::class          => "max_file_task_complexity",
		Domain_Search_Entity_Preview_Task_AttachToConversationMessage::class => "max_preview_task_complexity",
		Domain_Search_Entity_Preview_Task_AttachToThreadMessage::class       => "max_preview_task_complexity",
	];

	/**
	 * Ожидаемое число строк для вставки в поисковик за один вызов задачи.
	 *
	 * В рамках одного исполнения может быть обработано несколько задач сразу,
	 * эта настройка позволяет контролировать количество задач за одно исполнение.
	 *
	 * Логика следующая:
	 * — из очереди достаются задачи
	 * — задачи передаются в группировку перед вызовом исполнения
	 * — группировщик распределяет задачи по пачкам, с учетом сложности, определяемой этой настройкой.
	 */
	public static function perExecutionComplexityLimit(string $task_class):int {

		$config     = static::_load();
		$complexity = $config[static::_PER_EXECUTION_COMPLEXITY_LIMIT[$task_class] ?? 0] ?? 1;

		if ($complexity <= 0) {
			throw new ReturnFatalException("passed bad task per execution complexity value");
		}

		return $complexity;
	}

	/**
	 * Максимальная сложность задачи указанного типа.
	 * Если задача ставится с превышением этого лимита, она должна быть разделена на несколько задач.
	 */
	public static function maxTaskComplexity(string $task_class):int {

		$config     = static::_load();
		$complexity = $config[static::_MAX_TASK_COMPLEXITY[$task_class] ?? 0] ?? 0;

		if ($complexity <= 0) {
			throw new ReturnFatalException("passed bad task max complexity value");
		}

		return $complexity;
	}

	/**
	 * Загружает конфиг доступа к поиску.
	 */
	protected static function _load():array {

		$config = getConfig("SEARCH");
		return $config["task"];
	}
}