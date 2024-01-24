<?php

declare(strict_types = 1);

namespace Compass\Speaker;

/**
 * Агрегатор подписок на событие для домена звонков.
 */
class Domain_Call_Scenario_Event {

	/**
	 * Callback для события-проверки не прекратить ли мониторинг вызова для пользователя
	 *
	 * @throws \parseException
	 * @noinspection PhpUnusedParameterInspection
	 */
	#[Type_Task_Attribute_Executor(Type_Event_Call_MonitoringCheckRequired::EVENT_TYPE, Struct_Event_Call_MonitoringCheckRequired::class)]
	public static function onMonitoringCheckRequired(Struct_Event_Call_MonitoringCheckRequired $event_data):Type_Task_Struct_Response {

		// классы для мониторинга звонков
		$dialing_class      = Domain_Call_Entity_DialingMonitoringQueue::class;
		$establishing_class = Domain_Call_Entity_EstablishingMonitoringQueue::class;

		// получаем задачи для работы
		$dialing_queue_list      = Domain_Call_Entity_DialingMonitoringQueue::getList(100, 0);
		$establishing_queue_list = Domain_Call_Entity_EstablishingMonitoringQueue::getList(100, 0);

		// если нет звонков для наблюдения
		if (count($dialing_queue_list) < 1 && count($establishing_queue_list) < 1) {
			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_DONE);
		}

		// получаем задачи, которые нужно выполнить
		$dialing_work_queue_list      = self::_getQueueListForWork($dialing_queue_list);
		$establishing_work_queue_list = self::_getQueueListForWork($establishing_queue_list);

		// если задачи для исполнения отсутствуют
		if (count($dialing_work_queue_list) < 1 && count($establishing_work_queue_list) < 1) {
			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_NEXT_ITERATION_REQUIRED, time() + 5);
		}

		// получаем звонки, которые нужно завершить
		[$dialing_need_hand_up_call_for_users, $dialing_need_delete_for_call_and_users] = self::_getNeedHangUpCallsForUsers($dialing_work_queue_list, $dialing_class);
		[$establishing_need_hand_up_call_for_users, $establishing_need_delete_for_call_and_users] = self::_getNeedHangUpCallsForUsers($establishing_work_queue_list, $establishing_class);

		// удаляем те задачи, что уже неактуальны
		self::_deleteQueuesForUsers($dialing_class, $establishing_class, $dialing_need_delete_for_call_and_users, $establishing_need_delete_for_call_and_users);

		// для остальных - завершаем звонок
		self::_doHangUpCallsForUsers($dialing_class, $establishing_class, $dialing_need_hand_up_call_for_users, $establishing_need_hand_up_call_for_users);

		return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_NEXT_ITERATION_REQUIRED, time() + 1);
	}

	/**
	 * Получаем задачи для работы
	 */
	protected static function _getQueueListForWork(array $queue_list):array {

		// собираем те задачи, время которых уже пришло
		$work_queue_list = [];
		foreach ($queue_list as $row) {

			// если время еще не пришло
			if ($row["need_work"] > time()) {
				continue;
			}

			$work_queue_list[] = $row;
		}

		return $work_queue_list;
	}

	/**
	 * Получаем пользователей, звонки для которых нужно завершить
	 */
	protected static function _getNeedHangUpCallsForUsers(array $work_queue_list, string $monitoring_class):array {

		// собираем мапу звонков и айди пользователей, с которыми будем работать
		$call_map_list = array_column($work_queue_list, "call_map");
		$user_id_list  = array_column($work_queue_list, "user_id");
		$user_id_list  = array_map("intval", $user_id_list);

		/** @var Domain_Call_Entity_MonitoringQueueDefault $monitoring_class */
		return $monitoring_class::getUsersWhoNeedFinishCall($user_id_list, $call_map_list);
	}

	/**
	 * Удаляем неактуальные задачи для пользователей
	 */
	protected static function _deleteQueuesForUsers(string $dialing_class, string $establishing_class, array $dialing_need_delete_for_call_and_users, array $establishing_need_delete_for_call_and_users):void {

		$dialing_need_delete_queues[$dialing_class]           = $dialing_need_delete_for_call_and_users;
		$establishing_need_delete_queues[$establishing_class] = $establishing_need_delete_for_call_and_users;

		$need_delete_for_call_and_users = array_merge($dialing_need_delete_queues, $establishing_need_delete_queues);

		// для задач, за которыми уже не нужно следить
		/** @var Domain_Call_Entity_MonitoringQueueDefault $monitoring_class */
		foreach ($need_delete_for_call_and_users as $monitoring_class => $call_by_users) {

			foreach ($call_by_users as $call_map => $user_id_list) {
				$monitoring_class::deleteForUsers($user_id_list, $call_map);
			}
		}
	}

	/**
	 * Завершаем необходимые звонки
	 */
	protected static function _doHangUpCallsForUsers(string $dialing_class, string $establishing_class, array $dialing_need_hand_up_call_for_users, array $establishing_need_hand_up_call_for_users):void {

		$dialing_need_hand_up_call[$dialing_class]           = $dialing_need_hand_up_call_for_users;
		$establishing_need_hand_up_call[$establishing_class] = $establishing_need_hand_up_call_for_users;

		$need_hand_up_call_for_users = array_merge($dialing_need_hand_up_call, $establishing_need_hand_up_call);

		// вешаем трубку для пользователей
		foreach ($need_hand_up_call_for_users as $monitoring_class => $call_by_users) {

			foreach ($call_by_users as $call_map => $user_id_list) {

				foreach ($user_id_list as $user_id) {

					try {
						Helper_Calls::doHangup($user_id, $call_map, $monitoring_class::MONITORING_FINISH_REASON, "monitoring");
					} catch (cs_Call_IsFinished) { // если уже завершен, то ничего не делаем
					}
				}
			}
		}
	}

	/**
	 * Callback для события-проверки получения аналитики по звовнкам
	 *
	 * @throws \parseException
	 * @noinspection PhpUnusedParameterInspection
	 */
	#[Type_Task_Attribute_Executor(Type_Event_Call_GetCallAnalytics::EVENT_TYPE, Struct_Event_Call_GetCallAnalytics::class)]
	public static function onTryGetCallAnalytics(Struct_Event_Call_GetCallAnalytics $event_data):Type_Task_Struct_Response {

		$result = Domain_Call_Action_GetAndSaveCallsAnalytics::do();

		// если получение аналитики показывает, что больше аналитику не собираем, то останавливаем выполнение задачи сбора аналитики
		if ($result === false) {
			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_DONE);
		}

		// если всё ок, то отправляем в ответе, что нужно будет ещё повторить через N сек
		return Type_Task_Struct_Response::build(
			Type_Task_Handler::DELIVERY_STATUS_NEXT_ITERATION_REQUIRED,
			time() + Domain_Call_Action_GetAndSaveCallsAnalytics::INTERVAL
		);
	}
}
