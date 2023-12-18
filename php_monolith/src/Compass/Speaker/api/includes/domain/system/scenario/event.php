<?php

namespace Compass\Speaker;

/**
 * Агрегатор подписок на системные события
 */
class Domain_System_Scenario_Event {

	/**
	 * Callback для события синхронизации подписок в модуле и в go_event
	 *
	 * @param Struct_Event_System_SubscriptionsRefreshingRequested $event_data
	 *
	 * @return Type_Task_Struct_Response
	 * @throws \parseException
	 * @noinspection PhpUnusedParameterInspection
	 */
	#[Type_Attribute_EventListener(Type_Event_System_SubscriptionsRefreshingRequested::EVENT_TYPE)]
	#[Type_Task_Attribute_Executor(Type_Event_System_SubscriptionsRefreshingRequested::EVENT_TYPE, Struct_Event_System_SubscriptionsRefreshingRequested::class)]
	public static function onSubscriptionsRefreshingRequested(Struct_Event_System_SubscriptionsRefreshingRequested $event_data):Type_Task_Struct_Response {

		// отправляем подписки
		Domain_System_Action_Event_RefreshSubscriptions::do();
		return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_DONE);
	}
}
