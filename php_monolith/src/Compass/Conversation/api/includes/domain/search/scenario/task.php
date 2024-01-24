<?php

namespace Compass\Conversation;

/**
 * Сценарии задач для работы с поиском.
 */
class Domain_Search_Scenario_Task {

	/**
	 * Выполняет задачу подготовки сущностей к индексации.
	 */
	#[Type_Task_Attribute_Executor(Type_Event_Search_EntityPreparationQueue::EVENT_TYPE, Struct_Event_Search_EntityPreparationQueue::class)]
	public static function processEntityPreparationQueue():Type_Task_Struct_Response {

		try {

			$result_status = Domain_Search_Action_Queue_EntityPreparationWorker::run(5);
		} catch (\Exception $e) {

			$next_iteration_at = time() + Domain_Search_Config_Queue::getNextIterationDelayOnFail(Domain_Search_Config_Queue::ENTITY_REPARATION_QUEUE);

			// если произошла ошибка, то откладываем
			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_NEXT_ITERATION_REQUIRED, $next_iteration_at, $e->getMessage());
		}

		// если задача завершилась ошибкой
		if ($result_status === Domain_Search_Action_Queue_EntityPreparationWorker::STATUS_ERROR) {

			$next_iteration_at = time() + Domain_Search_Config_Queue::getNextIterationDelayOnFail(Domain_Search_Config_Queue::ENTITY_REPARATION_QUEUE);
			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_NEXT_ITERATION_REQUIRED, $next_iteration_at, "задачи завершились ошибкой");
		}

		// если очередь не разобрана
		if ($result_status === Domain_Search_Action_Queue_EntityPreparationWorker::STATUS_HAS_MORE) {

			$next_iteration_at = time() + Domain_Search_Config_Queue::getNextIterationDelayOnFilledQueue(Domain_Search_Config_Queue::ENTITY_REPARATION_QUEUE);
			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_NEXT_ITERATION_REQUIRED, $next_iteration_at);
		}

		// если очередь пустая
		if ($result_status === Domain_Search_Action_Queue_EntityPreparationWorker::STATUS_EMPTY) {

			$next_iteration_at = time() + Domain_Search_Config_Queue::getNextIterationDelayOnEmptyQueue(Domain_Search_Config_Queue::INDEX_FILLING_QUEUE);
			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_NEXT_ITERATION_REQUIRED, $next_iteration_at);
		}

		// вдруг пришел какой-то неожиданный статус
		$next_iteration_at = time() + Domain_Search_Config_Queue::getNextIterationDelayOnFail(Domain_Search_Config_Queue::ENTITY_REPARATION_QUEUE);
		return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_ERROR, $next_iteration_at, "got unknown status");
	}

	/**
	 * Выполняет задачу индексации сущностей.
	 */
	#[Type_Task_Attribute_Executor(Type_Event_Search_IndexFillingQueue::EVENT_TYPE, Struct_Event_Search_IndexFillingQueue::class)]
	public static function processIndexFillingQueue():Type_Task_Struct_Response {

		try {

			$result_status = Domain_Search_Action_Queue_IndexFillingWorker::run(5);
		} catch (\Exception $e) {

			$next_iteration_at = time() + Domain_Search_Config_Queue::getNextIterationDelayOnFail(Domain_Search_Config_Queue::INDEX_FILLING_QUEUE);

			// если произошла ошибка, то откладываем
			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_NEXT_ITERATION_REQUIRED, $next_iteration_at, $e->getMessage());
		}

		// если задача завершилась ошибкой
		if ($result_status === Domain_Search_Action_Queue_IndexFillingWorker::STATUS_ERROR) {

			$next_iteration_at = time() + Domain_Search_Config_Queue::getNextIterationDelayOnFail(Domain_Search_Config_Queue::INDEX_FILLING_QUEUE);
			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_NEXT_ITERATION_REQUIRED, $next_iteration_at, "задачи завершились ошибкой");
		}

		// если очередь не разобрана
		if ($result_status === Domain_Search_Action_Queue_IndexFillingWorker::STATUS_HAS_MORE) {

			$next_iteration_at = time() + Domain_Search_Config_Queue::getNextIterationDelayOnFilledQueue(Domain_Search_Config_Queue::INDEX_FILLING_QUEUE);
			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_NEXT_ITERATION_REQUIRED, $next_iteration_at);
		}

		// если очередь пустая
		if ($result_status === Domain_Search_Action_Queue_IndexFillingWorker::STATUS_EMPTY) {

			$next_iteration_at = time() + Domain_Search_Config_Queue::getNextIterationDelayOnEmptyQueue(Domain_Search_Config_Queue::INDEX_FILLING_QUEUE);
			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_NEXT_ITERATION_REQUIRED, $next_iteration_at);
		}

		// вдруг пришел какой-то неожиданный статус
		$next_iteration_at = time() + Domain_Search_Config_Queue::getNextIterationDelayOnFail(Domain_Search_Config_Queue::INDEX_FILLING_QUEUE);
		return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_ERROR, $next_iteration_at, "got unknown status");
	}
}