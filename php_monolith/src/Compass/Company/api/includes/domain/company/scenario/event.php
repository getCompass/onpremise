<?php

declare(strict_types=1);

namespace Compass\Company;

/**
 * Агрегатор подписок на событие для домена компании.
 */
class Domain_Company_Scenario_Event
{
	/**
	 * Callback для события - проверить не нужно ли запустить задачи по периодической рассылке данных для компаний
	 *
	 *
	 * @throws \parseException
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	#[Type_Task_Attribute_Executor(Type_Event_Company_SenderCheckRequired::EVENT_TYPE, Struct_Event_Company_SenderCheckRequired::class)]
	public static function onSenderCheckRequired(Struct_Event_Company_SenderCheckRequired $event_data): Type_Task_Struct_Response
	{

		$next_time = Type_Company_Job_Main::work();
		return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_NEXT_ITERATION_REQUIRED, $next_time);
	}

	/**
	 * Callback для события - проверить активность компании
	 *
	 *
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @noinspection PhpUnusedParameterInspection
	 */
	#[Type_Task_Attribute_Executor(Type_Event_Company_CheckLastActivity::EVENT_TYPE, Struct_Event_Company_CheckLastActivity::class)]
	public static function onCheckLastActivity(Struct_Event_Company_CheckLastActivity $event_data): Type_Task_Struct_Response
	{

		// если не требуется гибернация компании, то полностью завершаем работу ивента
		if (!\CompassApp\Company\HibernationHandler::instance()->isNeedHibernation()) {
			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_DONE);
		}

		// отправляем компанию в гибернацию если пришло время
		if (Domain_System_Entity_Hibernation::isNeedHibernate()) {
			Gateway_Socket_Pivot::startHibernate();
		}

		return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_NEXT_ITERATION_REQUIRED, time() + 300 + (COMPANY_ID % 100));
	}

	/**
	 * Callback для события - создания компании
	 *
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	#[Type_Attribute_EventListener(Type_Event_Company_CreateCompany::EVENT_TYPE)]
	public static function onCreateCompany(Struct_Event_Company_CreateCompany $event_data): void
	{

		foreach ($event_data->bot_list as $bot_info) {

			Domain_User_Action_AddBot::do(
				$bot_info["user_id"],
				$bot_info["npc_type"],
				"",
				$bot_info["full_name"],
				$bot_info["avatar_file_key"],
				""
			);
		}

		if ($event_data->is_enabled_employee_card) {

			Domain_Company_Entity_Config::set(Domain_Company_Entity_Config::MODULE_EXTENDED_EMPLOYEE_CARD_KEY, $event_data->is_enabled_employee_card);
			Domain_Company_Action_SendExtendedCardEvent::do($event_data->creator_user_id, $event_data->is_enabled_employee_card);
		}
	}
}
