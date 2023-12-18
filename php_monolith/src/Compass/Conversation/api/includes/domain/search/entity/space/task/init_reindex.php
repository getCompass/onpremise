<?php declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Первый этап полной индексации пространства.
 * Полностью очищает все данные индекса и начинает наполнять их новыми.
 */
class Domain_Search_Entity_Space_Task_InitReindex extends Domain_Search_Entity_Task {

	public const TASK_TYPE = Domain_Search_Const::TASK_TYPE_SPACE_INIT_REINDEX;

	/**
	 * Заряжает задачу по восстановлению search_rel для сущностей,
	 * которые не могут быть восстановлены при переиндексации диалогов.
	 */
	public static function queue():void {

		$task_data = new Struct_Domain_Search_IndexTask_Entity("0");
		$entity    = new Struct_Domain_Search_AppEntity(Domain_Search_Const::TYPE_SPACE, "space");

		static::say("запустил задачу на полную индексацию");
		static::_queue([$entity], [Struct_Domain_Search_Task::fromDeclaration(static::TASK_TYPE, $task_data)]);
	}

	/**
	 * Выполняет указанный список задач.
	 *
	 * @param Struct_Domain_Search_Task[] $task_list
	 */
	public static function execList(array $task_list):void {

		Gateway_Search_Main::truncate();
		Domain_Search_Entity_Space_Task_ReindexConversations::queue();
	}
}

