<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use CompassApp\Pack\Main;

/**
 * Базовый класс для задачи индексации.
 */
abstract class Domain_Search_Entity_Task {

	/** @var int тип задачи, должен быть уникальным */
	public const TASK_TYPE = 0;

	/** @var int сколько задач указанного типа можно брать на индексацию в одну итерацию */
	protected const _PER_ITERATION_LIMIT = 1;

	/** @var int сколько ошибок допустимо для задачи этого типа */
	protected const _ERROR_LIMIT = 0;

	/** @var Gateway_Db_SpaceSearch_Queue_Abstract[] Шлюз для работы с очередью для каждого из типа задач */
	private const _QUEUE_GATEWAY_BY_TASK_TYPE_REL = [
		Domain_Search_Entity_Space_Task_InitReindex::TASK_TYPE                   => Gateway_Db_SpaceSearch_Queue_EntityPreparationQueue::class,
		Domain_Search_Entity_Space_Task_ReindexConversations::TASK_TYPE          => Gateway_Db_SpaceSearch_Queue_EntityPreparationQueue::class,
		// Domain_Search_Entity_Conversation_Task_Index::TASK_TYPE                  => Gateway_Db_SpaceSearch_Queue_EntityPreparationQueue::class,
		Domain_Search_Entity_Conversation_Task_Reindex::TASK_TYPE                => Gateway_Db_SpaceSearch_Queue_EntityPreparationQueue::class,
		Domain_Search_Entity_Conversation_Task_Clear::TASK_TYPE                  => Gateway_Db_SpaceSearch_Queue_EntityPreparationQueue::class,
		Domain_Search_Entity_Conversation_Task_Purge::TASK_TYPE                  => Gateway_Db_SpaceSearch_Queue_EntityPreparationQueue::class,
		Domain_Search_Entity_Thread_Task_Reindex::TASK_TYPE                      => Gateway_Db_SpaceSearch_Queue_EntityPreparationQueue::class,
		Domain_Search_Entity_ConversationMessage_Task_Index::TASK_TYPE           => Gateway_Db_SpaceSearch_Queue_IndexTaskQueue::class,
		Domain_Search_Entity_ConversationMessage_Task_Reindex::TASK_TYPE         => Gateway_Db_SpaceSearch_Queue_EntityPreparationQueue::class,
		Domain_Search_Entity_ConversationMessage_Task_Hide::TASK_TYPE            => Gateway_Db_SpaceSearch_Queue_EntityPreparationQueue::class,
		Domain_Search_Entity_ConversationMessage_Task_Delete::TASK_TYPE          => Gateway_Db_SpaceSearch_Queue_EntityPreparationQueue::class,
		Domain_Search_Entity_ThreadMessage_Task_Index::TASK_TYPE                 => Gateway_Db_SpaceSearch_Queue_IndexTaskQueue::class,
		Domain_Search_Entity_ThreadMessage_Task_Reindex::TASK_TYPE               => Gateway_Db_SpaceSearch_Queue_EntityPreparationQueue::class,
		Domain_Search_Entity_ThreadMessage_Task_Hide::TASK_TYPE                  => Gateway_Db_SpaceSearch_Queue_EntityPreparationQueue::class,
		Domain_Search_Entity_ThreadMessage_Task_Delete::TASK_TYPE                => Gateway_Db_SpaceSearch_Queue_EntityPreparationQueue::class,
		Domain_Search_Entity_Preview_Task_AttachToConversationMessage::TASK_TYPE => Gateway_Db_SpaceSearch_Queue_IndexTaskQueue::class,
		Domain_Search_Entity_Preview_Task_AttachToThreadMessage::TASK_TYPE       => Gateway_Db_SpaceSearch_Queue_IndexTaskQueue::class,
		Domain_Search_Entity_File_Task_Index::TASK_TYPE                          => Gateway_Db_SpaceSearch_Queue_EntityPreparationQueue::class,
		Domain_Search_Entity_File_Task_Reindex::TASK_TYPE                        => Gateway_Db_SpaceSearch_Queue_EntityPreparationQueue::class,
		Domain_Search_Entity_File_Task_AttachToConversationMessage::TASK_TYPE    => Gateway_Db_SpaceSearch_Queue_IndexTaskQueue::class,
		Domain_Search_Entity_File_Task_AttachToThreadMessage::TASK_TYPE          => Gateway_Db_SpaceSearch_Queue_IndexTaskQueue::class,
	];

	/**
	 * Выполняет пачку задач указанного типа.
	 *
	 * @param Struct_Domain_Search_Task[] $task_list
	 */
	abstract public static function execList(array $task_list):void;

	/**
	 * Добавляет сущности и задачу для индексации
	 *
	 * @param Struct_Domain_Search_AppEntity[] $entity_list
	 * @param Struct_Domain_Search_Task[]      $task_list
	 */
	final protected static function _queue(array $entity_list = [], array $task_list = []):void {

		// индексация доступна только в определенных пространствах
		if (!Domain_Search_Config_Index::isSpaceAllowed(COMPANY_ID)) {
			return;
		}

		// раз ничего не пришло
		if (count($entity_list) === 0 && count($task_list) === 0) {
			return;
		}

		// проверяем что в entity_list не передали левак
		self::_throwIfIncorrectEntityList($entity_list);

		// группируем задачи по классу шлюза, который будет использован для сохранения задач в очередь
		$task_list_grouped_by_type = self::_groupQueueTaskListByQueueGateway($task_list);

		/** начинаем транзакцию */
		Gateway_Db_SpaceSearch_Main::beginTransaction();

		try {

			// фильтруем и оставляем только те entity_list, которые не существуют
			// для них мы создадим записи
			// функция _filterUnfoundEntityList должна в большинстве случаев попадать во внутренний кэш
			// поскольку под капотом используется ProxyCache
			$unfound_entity_list = self::_filterUnfoundEntityList($entity_list);
			count($unfound_entity_list) > 0 && Gateway_Db_SpaceSearch_EntitySearchIdRel::insertList($unfound_entity_list);

			count($task_list) > 0 && static::say("добавляю %d задач в очередь", count($task_list));

			/**
			 * @var Gateway_Db_SpaceSearch_Queue_Abstract $queue_handler обработчик с помощью которого сохраним таски в нужную очередь
			 */
			foreach ($task_list_grouped_by_type as $queue_handler => $grouped_task_list) {
				$queue_handler::insert($grouped_task_list);
			}
		} catch (\Exception) {

			// пока консервативно относимся к ошибкам
			// и ничего не делаем, задача добавилась с ошибкой
			Gateway_Db_SpaceSearch_Main::rollback();
			return;
		}

		Gateway_Db_SpaceSearch_Main::commitTransaction();
		/** завершаем транзакцию */
	}

	/**
	 * Проверяем, что при создании объекта entity не передали откровенный левак
	 *
	 * @param Struct_Domain_Search_AppEntity[] $entity_list
	 */
	final protected static function _throwIfIncorrectEntityList(array $entity_list):void {

		foreach ($entity_list as $entity) {

			// если идентификатор сущности не является map'ой, то ничего не проверяем
			if (!in_array($entity->entity_type, array_keys(Domain_Search_Const::ENTITY_TYPE_MAP_ENTITY_REL))) {
				continue;
			}

			// получаем тип сущности из мапы
			$map_entity = Main::getEntityType($entity->entity_map);

			// проверяем, что это совпадает с типом сущности из самого объекта
			if (Domain_Search_Const::ENTITY_TYPE_MAP_ENTITY_REL[$entity->entity_type] !== $map_entity) {
				throw new ParseFatalException("attempt to create Search_AppEntity with missmatch between enitty_type and entity_map");
			}
		}
	}

	/**
	 * Группируем задачи по классу шлюза, который будет использован для сохранения задач в очередь
	 *
	 * @return array
	 */
	final protected static function _groupQueueTaskListByQueueGateway(array $task_list):array {

		// группируем задачи по обработчику
		$task_list_grouped_by_type = [];
		foreach ($task_list as $task) {

			// получаем обработчику по типу
			$queue_handler = self::_QUEUE_GATEWAY_BY_TASK_TYPE_REL[$task->type];

			// группируем по обработчику
			$task_list_grouped_by_type[$queue_handler][] = $task;
		}

		return $task_list_grouped_by_type;
	}

	/**
	 * Фильтруем entity_list и оставляем только те, что не найдутся в хранилище
	 *
	 * @param Struct_Domain_Search_AppEntity[] $entity_list
	 *
	 * @return array
	 */
	protected static function _filterUnfoundEntityList(array $entity_list):array {

		// если пришла пустота, то вернем ее в ответе
		if (count($entity_list) < 1) {
			return $entity_list;
		}

		$found_entity_list = Domain_Search_Repository_ProxyCache_EntitySearchId::load($entity_list);

		// сюда сложим все не найденные entity
		$unfound_entity_list = [];
		foreach ($entity_list as $entity) {

			// проверяем существование такого entity
			if (!isset($found_entity_list[$entity->entity_map])) {
				$unfound_entity_list[] = $entity;
			}
		}

		return $unfound_entity_list;
	}

	/**
	 * Возвращает допустимый лимит ошибок
	 */
	public static function getErrorLimit():int {

		return static::_ERROR_LIMIT;
	}

	/**
	 * Группирует задачи по массивам.
	 *
	 * Например из очереди приходит 10 задач индексации одного диалога
	 * для разных пользователей, этот метод их корректно группирует в пачку на итерацию индексации.
	 */
	public static function splitIntoChunks(array $full_task_list):array {

		// по хорошему вызов должен выглядеть так:
		// static::_splitTaskList($full_task_list, static::PER_ITERATION_LIMIT, static fn() => 1);
		// но это слишком медленно, поэтому дефолтная логика максимально простая
		return array_chunk($full_task_list, static::_PER_ITERATION_LIMIT);
	}

	/**
	 * Функция распределения задач по чанкам.
	 * С помощью нее можно чанки раскидывать с учетом суммарной сложности задач.
	 *
	 * По итогу задачу всегда берутся с небольшим превышением лимита.
	 */
	protected static function _distributeTasksByComplexityEstimate(array $full_task_list, int $complexity_limit, callable $complexity_fn):array {

		$chunk_list = [];

		// данные текущего чанка
		$current_chunk            = [];
		$current_chunk_complexity = 0;

		foreach ($full_task_list as $task_data) {

			// считаем сложность пачки с задачами
			// сложность не должна сильно превышать лимит, чтобы ничего не сломалось
			$current_chunk[]          = $task_data;
			$current_chunk_complexity += $complexity_fn($task_data);

			if ($current_chunk_complexity > $complexity_limit) {

				$chunk_list[] = $current_chunk;

				$current_chunk            = [];
				$current_chunk_complexity = 0;
			}
		}

		// не теряем последний чанк
		if (count($current_chunk) > 0) {
			$chunk_list[] = $current_chunk;
		}

		return $chunk_list;
	}

	/**
	 * Функция логирования для задачи поиска.
	 */
	final public static function say(string $message, mixed ...$args):void {

		$source  = str_replace("Domain_Search_Entity", "", static::class);
		$time    = date("H:i:s", time());
		$message = sprintf($message, ...$args);

		Type_System_Admin::log("indexation-log", COMPANY_ID . ": [INFO] $time: $source — $message");
	}

	/**
	 * Функция логирования для задачи поиска.
	 */
	final public static function yell(string $message, mixed ...$args):void {

		$source  = str_replace("Domain_Search_Entity", "", static::class);
		$time    = date("H:i:s", time());
		$message = sprintf($message, ...$args);

		Type_System_Admin::log("indexation-log", COMPANY_ID . ": [WARN] $time: $source — $message");
	}
}