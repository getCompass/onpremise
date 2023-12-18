<?php

namespace Compass\Conversation;

/**
 * Класс для разбора очереди задач индексации данных.
 */
class Domain_Search_Action_Queue_IndexFillingWorker extends Domain_Search_Action_Queue_AbstractWorker {

	/**
	 * Класс-интерфейс через который осуществляется работа с очередью
	 * @var Gateway_Db_SpaceSearch_Queue_Abstract
	 */
	protected const _QUEUE_GATEWAY = Gateway_Db_SpaceSearch_Queue_IndexTaskQueue::class;

	/** @var string название очереди */
	protected const _WORKER_QUEUE_NAME = Domain_Search_Config_Queue::INDEX_FILLING_QUEUE;

	/** @var string префикс для лог-файла воркера */
	protected const _WORKER_LOG_FILE_PREFIX = "index-filling";

	/** @var string label метрики воркера */
	protected const _WORKER_METRIC_LABEL = "index-filling";

	/** @var string префикс названия метрики воркера */
	protected const _WORKER_METRIC_NAME_PREFIX = "index-filling";
}
