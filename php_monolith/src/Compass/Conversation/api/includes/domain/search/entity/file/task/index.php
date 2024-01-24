<?php declare(strict_types=1);

namespace Compass\Conversation;

/**
 * Задача индексации файлов.
 *
 * Нужен для работы непосредственно с файлами
 * как отдельными сущностями, а не с прикрепленными файлами к сообщениям.
 */
class Domain_Search_Entity_File_Task_Index extends Domain_Search_Entity_Task {

	/** @var int тип задачи */
	public const TASK_TYPE = Domain_Search_Const::TASK_TYPE_FILE_INDEX;

	/**
	 * Добавляет задачу в очередь индексации.
	 *
	 * К файлу привязывается search_id сразу после загрузки,
	 * при этом никакие данные пока не нужно индексировать.
	 *
	 * Если появится поиск файлов, то нужно будет проиндексировать имя.
	 */
	public static function queueList(array $file_map_list):void {

		// формируем список сущностей
		$entity_list = array_map(
			static fn(string $el) => new Struct_Domain_Search_AppEntity(Domain_Search_Const::TYPE_FILE, $el),
			$file_map_list
		);

		// вставляем данные
		static::_queue($entity_list);
	}

	/**
	 * @inheritDoc
	 */
	public static function execList(array $task_list):void {

		// задачи по индексации нет, если появится необходимость
		// индексировать файлы, то это можно реализовать тут
	}
}
