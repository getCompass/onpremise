<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\CompanyNotServedException;

/**
 * Класс обработки сценариев событий из php_jitsi
 */
class Domain_Jitsi_Scenario_Event {

	/**
	 * Отправить сообщение с ссылкой на сингл звонок
	 *
	 * @long
	 *
	 * @param Struct_Event_Jitsi_AddMediaConferenceMessage $event_data
	 *
	 * @return Type_Task_Struct_Response
	 * @throws ParseFatalException
	 * @throws CompanyNotServedException
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyIsHibernate
	 */
	#[Type_Task_Attribute_Executor(Type_Event_Jitsi_AddMediaConferenceMessage::EVENT_TYPE, Struct_Event_Jitsi_AddMediaConferenceMessage::class)]
	public static function onAddMediaConferenceMessage(Struct_Event_Jitsi_AddMediaConferenceMessage $event_data):Type_Task_Struct_Response {

		try {
			$space = Domain_Company_Entity_Company::get($event_data->space_id);
			Gateway_Socket_Conversation::addMediaConferenceMessage(
				$space, $event_data->user_id, $event_data->conversation_map, $event_data->conference_id,
				$event_data->accept_status, $event_data->link, $event_data->conference_code, $event_data->opponent_user_id
			);
		} catch (cs_CompanyNotExist|ReturnFatalException) {
			// ничего не делаем
		}

		return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_DONE);
	}
}
