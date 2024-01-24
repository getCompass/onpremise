<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\ExceptionUtils;

/**
 * Класс для разбора очереди задач индексации данных.
 */
abstract class Domain_Search_Action_Queue_AbstractWorker {

	/** @var int результат работы — очередь разобрана */
	public const STATUS_EMPTY = 1;

	/** @var int результат работы — в очереди есть задачи */
	public const STATUS_HAS_MORE = 2;

	/** @var int результат работы — ошибка */
	public const STATUS_ERROR = 3;

	/** @var int кол-во задач итерируемые за раз */
	protected const _PER_ITERATION_COUNT = 300;

	/**
	 * Класс-интерфейс через который осуществляется работа с очередью
	 * @var Gateway_Db_SpaceSearch_Queue_Abstract
	 */
	protected const _QUEUE_GATEWAY = Gateway_Db_SpaceSearch_Queue_Abstract::class;

	/** @var string название очереди */
	protected const _WORKER_QUEUE_NAME = "";

	/** @var string префикс для лог-файла воркера */
	protected const _WORKER_LOG_FILE_PREFIX = "";

	/** @var string label метрики воркера */
	protected const _WORKER_METRIC_LABEL = "";

	/** @var string префикс названия метрики воркера */
	protected const _WORKER_METRIC_NAME_PREFIX = "";

	/**
	 * Выбирает N-задач для индексации и запускает их в работу.
	 * Возвращает флаг — есть ли еще задачи в очереди.
	 *
	 * @param int $max_timeout
	 *
	 * @return int
	 * @throws Domain_Search_Exception_IndexationUnallowable
	 * @throws ReturnFatalException
	 * @throws Domain_Search_Exception_IndexationFailed
	 *
	 * @long большой
	 */
	public static function run(int $max_timeout):int {

		// выставляем лимит по памяти и максимальное время работы
		ini_set("memory_limit", Domain_Search_Config_Queue::getIniMemoryLimit(static::_WORKER_QUEUE_NAME));
		$work_till = time() + min($max_timeout, Domain_Search_Config_Queue::getExecutionTimeLimit(static::_WORKER_QUEUE_NAME));

		while (time() <= $work_till && static::_checkRam()) {

			// получаем задачки на текущую итерацию, обновляем список каждую итерацию
			// поскольку ожидаем, что большая часть задач будет связана с сообщениями
			// и выполняться будет одной пачкой полностью (но это не точно)
			$task_list = static::_QUEUE_GATEWAY::getForWork(static::_PER_ITERATION_COUNT);

			// если задач нет, то просто завершаем работу
			if (count($task_list) === 0) {
				return static::STATUS_EMPTY;
			}

			// убеждаемся, что в списке не застрявших задач
			$task_list = static::_removeFailed($task_list);

			// если все выбранные задачи с ошибкой,
			// то идем на следующую итерацию
			if (count($task_list) === 0) {
				continue;
			}

			// сортируем задачи по идентификаторам, мало ли в каком порядке нам их шлюз вернул
			usort($task_list, static fn(Struct_Domain_Search_Task $a, Struct_Domain_Search_Task $b) => $a->task_id <=> $b->task_id);

			// сюда запишем массив тасков, сгруппированных по типу
			// в том порядке, в котором их нужно выполнить
			$task_list_grouped_by_type = static::_makeSequenceGroupByTaskType($task_list);

			// начинаем пробегаться по каждой группе тасков
			/** @var Struct_Domain_Search_Task[] $grouped_task_list */
			foreach ($task_list_grouped_by_type as $grouped_task_list) {

				if (time() > $work_till || !static::_checkRam()) {
					break;
				}

				// получаем типа таска
				$task_type = $grouped_task_list[0]->type;

				// бьем список задач по правилам самой задачи
				$current_task_chunk_list = Domain_Search_Entity_TaskHandler::splitIntoChunks($task_type, $grouped_task_list);

				// запускаем выполнение тасков
				while (time() <= $work_till && count($current_task_chunk_list) > 0 && static::_checkRam()) {

					// получаем текущий чанк задач и передаем его в работу
					$current_task_chunk = array_shift($current_task_chunk_list);
					$result             = static::_processChunk($task_type, $current_task_chunk);

					// если чанк обработался некорректно
					// то останавливаем обработку в текущей итерации
					if ($result === false) {
						return static::STATUS_ERROR;
					}
				}
			}
		}

		// смотрим, сколько там еще задач в очереди
		if (!static::_QUEUE_GATEWAY::isHasMoreTasks()) {
			return static::STATUS_EMPTY;
		}

		static::_logQueueLength(COMPANY_ID . " есть задачи в очереди");
		\BaseFrame\Monitor\Core::metric(static::_getWorkerMetricName("index_queue_length"), 1);

		return static::STATUS_HAS_MORE;
	}

	/**
	 * Проверяет, что список задач можно брать в работу.
	 *
	 * @throws Domain_Search_Exception_IndexationUnallowable
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws Domain_Search_Exception_IndexationFailed
	 */
	protected static function _removeFailed(array $task_list):array {

		$need_drop_task_id_list = [];
		foreach ($task_list as $index => $task) {

			if ($task->error_count > Domain_Search_Entity_TaskHandler::getErrorLimit($task->type)) {

				if (!Domain_Search_Config_Queue::isFailAllowed(static::_WORKER_QUEUE_NAME)) {
					throw new Domain_Search_Exception_IndexationFailed("task $task->task_id has too many errors");
				}

				$message = COMPANY_ID . ": drop $task->task_id from queue — error limit exceeded $task->error_count ";
				static::_logFailure($message . var_export($task, true));

				$need_drop_task_id_list[] = $task->task_id;
				unset($task_list[$index]);
			}
		}

		$dropped_task_count = count($need_drop_task_id_list);

		// удаляем задачи разом, если они есть
		$dropped_task_count > 0 && static::_QUEUE_GATEWAY::delete($need_drop_task_id_list);

		// логируем ошибку
		$dropped_task_count > 0 && \BaseFrame\Monitor\Core::log("dropped $dropped_task_count due to error limit")
			->label("space_id", COMPANY_ID)
			->label("domain", "search")
			->label("worker", static::_getWorkerMetricLabel())
			->seal();

		return $task_list;
	}

	/**
	 * Группируем таски по типу, при этом не нарушаем последовательность тасков!
	 * Например в функцию пришли задачи с типами: [1, 1, 2, 10, 20, 10] (для простоты и лаконичности примера пишу только тип таска)
	 * То на выходе должны вернуть:
	 * [
	 *    [1, 1,],
	 *    [2],
	 *    [10],
	 *    [20],
	 *    [10],
	 * ]
	 * Группируем только последовательные таски с одинаковым типом!
	 *
	 * @param Struct_Domain_Search_Task[] $task_list
	 *
	 * @return array
	 */
	protected static function _makeSequenceGroupByTaskType(array $task_list):array {

		// сюда будем записывать последний тип проитерированного таска
		// по умолчанию – тип первого таска из очереди
		$last_iterate_task_type = $task_list[0]->type;

		// двумерный массив
		// сюда будем записывать массив тасков, сгруппированных по типу
		// в том порядке, в котором их нужно выполнить
		$task_list_grouped_by_type = [];
		$task_list_group_index     = 0;

		// начинаем пробегаться чтобы сгруппировать таски
		foreach ($task_list as $task) {

			// если предыдущий таск и текущий имеют разные типы
			if ($task->type !== $last_iterate_task_type) {

				// повышаем индекс в массиве со сгруппированными тасками
				$task_list_group_index += 1;

				// записываем последний проитерированный тип таск
				$last_iterate_task_type = $task->type;
			}

			// группируем таск
			$task_list_grouped_by_type[$task_list_group_index][] = $task;
		}

		return $task_list_grouped_by_type;
	}

	/**
	 * Выполняет пачку однотипных задач.
	 * Изменения в базу данных.
	 *
	 * @param Struct_Domain_Search_Task[] $current_chunk
	 *
	 * @throws Domain_Search_Exception_IndexationFailed
	 */
	protected static function _processChunk(int $task_type, array $current_chunk):bool {

		try {

			static::_QUEUE_GATEWAY::update(array_column($current_chunk, "task_id"), [
				"error_count" => "error_count + 1",
				"updated_at"  => time(),
			]);

			// передаем текущую пачку задач в работу
			Domain_Search_Entity_TaskHandler::handleList($task_type, $current_chunk);
		} catch (\Exception|\Error $e) {

			// формируем полное сообщение об ошибке
			$message = ExceptionUtils::makeMessage($e, 500);

			// записываем в лог и выкидываем исключение, если нужно
			static::_logFailure(COMPANY_ID . ": indexation failure: {$message}");
			!Domain_Search_Config_Queue::isFailAllowed(static::_WORKER_QUEUE_NAME) && throw new Domain_Search_Exception_IndexationFailed($e->getMessage());

			return false;
		}

		// задачи завершены, удаляем
		static::_QUEUE_GATEWAY::delete(array_column($current_chunk, "task_id"));
		return true;
	}

	/**
	 * Логируем размер очереди
	 *
	 * @throws ParseFatalException
	 */
	final protected static function _logQueueLength(string $text):void {

		$log_file_name = static::_getWorkerFileLogPrefix() . "_indexation-task-count";
		Type_System_Admin::log($log_file_name, $text);
	}

	/**
	 * Логируем провал
	 *
	 * @throws ParseFatalException
	 */
	final protected static function _logFailure(string $text):void {

		$log_file_name = static::_getWorkerFileLogPrefix() . "_indexation-failure";
		Type_System_Admin::log($log_file_name, $text);
	}

	/**
	 * Получаем префикс для названия лог файла воркера
	 *
	 * @return string
	 * @throws ParseFatalException
	 */
	final protected static function _getWorkerFileLogPrefix():string {

		if (mb_strlen(static::_WORKER_LOG_FILE_PREFIX) < 1) {
			throw new ParseFatalException("concrete worker log file prefix");
		}

		return static::_WORKER_LOG_FILE_PREFIX;
	}

	/**
	 * Получаем label для метрики воркера
	 *
	 * @return string
	 * @throws ParseFatalException
	 */
	final protected static function _getWorkerMetricLabel():string {

		if (mb_strlen(static::_WORKER_METRIC_LABEL) < 1) {
			throw new ParseFatalException("concrete worker metric label");
		}

		return static::_WORKER_METRIC_LABEL;
	}

	/**
	 * Получаем название метрики воркера
	 *
	 * @return string
	 * @throws ParseFatalException
	 */
	final protected static function _getWorkerMetricName(string $metric_name):string {

		if (mb_strlen(static::_WORKER_METRIC_NAME_PREFIX) < 1) {
			throw new ParseFatalException("concrete worker metric name");
		}

		return static::_WORKER_METRIC_NAME_PREFIX . "_" . $metric_name;
	}

	/**
	 * Проверяем ограничение памяти.
	 */
	final protected static function _checkRam():bool {

		$limit      = Domain_Search_Config_Queue::getIniMemoryLimit(static::_WORKER_QUEUE_NAME);
		$multiplier = Domain_Search_Config_Queue::getMemoryPercentLimit(static::_WORKER_QUEUE_NAME);

		$limit_scale = mb_substr($limit, -1);
		$limit_value = mb_substr($limit, 0, -1);

		$limit_bytes = match ($limit_scale) {
			"K" => $limit_value * 1024,
			"M" => $limit_value * 1024 * 1024,
			"G" => $limit_value * 1024 * 1024 * 1024,
			default => throw new ParseFatalException("incorrect ram limit value")
		};

		$used_bytes  = memory_get_usage();
		$limit_bytes = $limit_bytes * $multiplier;

		$result = $used_bytes < $limit_bytes;

		if (!$result) {

			$log_file_name = static::_getWorkerFileLogPrefix() . "-ram-control";
			Type_System_Admin::log($log_file_name, "ram limit is close ({$used_bytes} bytes from {$limit_bytes} bytes), stopping");
		}

		return $result;
	}
}
