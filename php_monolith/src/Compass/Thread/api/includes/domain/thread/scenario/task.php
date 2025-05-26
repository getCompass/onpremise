<?php

namespace Compass\Thread;

/**
 * Агрегатор подписок на таски для домена thread.
 * Класс обработки сценариев тасков.
 */
class Domain_Thread_Scenario_Task {

	/**
	 * Очищаем прочитавших участников старше 2-ух недель
	 *
	 * @return Type_Task_Struct_Response
	 * @throws \parseException
	 */
	#[Type_Task_Attribute_Executor(Type_Event_Thread_ClearExpiredMessageReadParticipants::EVENT_TYPE, Struct_Event_Thread_ClearExpiredMessageReadParticipants::class)]
	public static function clearExpiredMessageReadParticipants():Type_Task_Struct_Response {

		$time_delete_to = time() - DAY14;

		try {

			for ($table_shard = 1; $table_shard <= 12 ; $table_shard++) {
				Gateway_Db_CompanyThread_MessageReadParticipants::deleteByMessageCreatedAt($table_shard, $time_delete_to);
			}
		} catch (\BaseFrame\Exception\Gateway\QueryFatalException) {
			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_ERROR, time() + 60, "got query exception");
		}

		return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_DONE);
	}
}