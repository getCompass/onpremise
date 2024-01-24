<?php declare(strict_types=1);

namespace Compass\Conversation;

/**
 * Второй этап полной индексации пространства.
 * Задача начинает переиндексацию всех диалогов.
 */
class Domain_Search_Entity_Space_Task_ReindexConversations extends Domain_Search_Entity_Task {

	public const TASK_TYPE = Domain_Search_Const::TASK_TYPE_SPACE_REINDEX_CONVERSATIONS;

	/** @var int сколько диалогов за одну итерацию можно поставить в очередь */
	protected const _CONVERSATION_PER_STEP_COUNT = 1000;

	/**
	 * Заряжает задачу по восстановлению search_rel для сущностей,
	 * которые не могут быть восстановлены при переиндексации диалогов.
	 */
	public static function queue():void {

		$task_data = new Struct_Domain_Search_IndexTask_Entity("0", static::_initTaskExtra());
		static::_queue(task_list: [Struct_Domain_Search_Task::fromDeclaration(static::TASK_TYPE, $task_data)]);
	}

	/**
	 * Выполняет указанный список задач.
	 *
	 * @param Struct_Domain_Search_Task[] $task_list
	 */
	public static function execList(array $task_list):void {

		$task = $task_list[0];

		$task_data  = new Struct_Domain_Search_IndexTask_Entity(...$task->data);
		$task_extra = $task_data->data;

		// получаем диалоги для индексации
		// используем Dymanic, а не Meta, поскольку из меты нельзя восстановить conversation_map
		$next_offset = static::_getNextOffset($task_extra);
		$fetched     = Gateway_Db_CompanyConversation_ConversationDynamic::getOrdered(static::_CONVERSATION_PER_STEP_COUNT, $next_offset);

		// ставим диалоги в очередь индексации
		$conversation_map_list = array_column($fetched, "conversation_map");
		Domain_Search_Entity_Conversation_Task_Reindex::queueList($conversation_map_list);

		// если в бд больше нет записей, то завершаем работу
		if (count($fetched) < static::_CONVERSATION_PER_STEP_COUNT) {
			return;
		}

		// увеличиваем офсет для следующей задачи
		$next_offset += static::_CONVERSATION_PER_STEP_COUNT;
		$task_extra  = static::_setNextOffset($task_extra, $next_offset);

		// ставим еще одну итерацию задачи, но с новыми экстра-данными
		$task_data = new Struct_Domain_Search_IndexTask_Entity("0", $task_extra);
		static::_queue(task_list: [Struct_Domain_Search_Task::fromDeclaration(static::TASK_TYPE, $task_data)]);
	}

	# region работа с экстра-данными задачи

	/**
	 * Инициализирует структуру с экстра-данными задачи.
	 */
	protected static function _initTaskExtra():array {

		return [
			"last_offset" => 0,
		];
	}

	/**
	 * Возвращает последнее значение смещения для файлов
	 */
	protected static function _getNextOffset(array $extra):int {

		return $extra["last_offset"];
	}

	/**
	 * Устанавливает офсет последней выборки блоков.
	 */
	protected static function _setNextOffset(array $extra, int $offset):array {

		$extra["last_offset"] = $offset;
		return $extra;
	}

	# endregion работа с экстра-данными задачи
}

