<?php

namespace Compass\Pivot;

/**
 * Класс обработки сценариев событий партнёров.
 */
class Domain_Pivot_Scenario_Event {

	/**
	 * Отправлена задача для партнёрки.
	 *
	 * @long
	 *
	 * @param Struct_Event_SpaceTariff_SpaceUnblock $event_data
	 *
	 * @return Type_Task_Struct_Response
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	#[Type_Task_Attribute_Executor(Type_Event_SpaceTariff_SpaceUnblock::EVENT_TYPE, Struct_Event_SpaceTariff_SpaceUnblock::class)]
	public static function onSpaceUnblocked(Struct_Event_SpaceTariff_SpaceUnblock $event_data):Type_Task_Struct_Response {

		// если прошло время проверки - завершаем выполнение
		if ($event_data->check_until < time()) {

			$message = ":exclamation: В пространстве $event_data->space_id не закончилась разблокировка пространства";

			// отправляем в Compass
			Domain_SpaceTariff_Entity_Alert::send($message);
			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_DONE);
		}

		$company = Domain_Company_Entity_Company::get($event_data->space_id);

		$is_unblocked = Gateway_Socket_Company::checkIsUnblocked($company->company_id, $company->domino_id, Domain_Company_Entity_Company::getPrivateKey($company->extra));

		if (!$is_unblocked) {
			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_NEXT_ITERATION_REQUIRED);
		}

		return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_DONE);
	}
}