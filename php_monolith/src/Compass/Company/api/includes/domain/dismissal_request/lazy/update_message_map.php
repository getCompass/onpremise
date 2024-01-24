<?php

namespace Compass\Company;

/**
 * Ленивое обновление связанных сообщения для заявок на увольнение.
 * Появилось в результате обновления заявок и добавления в них поля message_map.
 */
class Domain_DismissalRequest_Lazy_UpdateMessageMap {

	/**
	 * Возвращает все заявки, которые нужно обновить.
	 * Обновлять нужно заявки, у которых нет привязанного сообщения.
	 *
	 * @param Struct_Db_CompanyData_DismissalRequest[] $dismissal_request_list
	 *
	 * @return Struct_Db_CompanyData_DismissalRequest[]
	 */
	public static function process(array $dismissal_request_list):array {

		$to_update_dismissal_request_id_list = [];
		$to_update_dismissal_request_list    = [];

		$date_from = time();

		foreach ($dismissal_request_list as $dismissal_request) {

			if (Domain_DismissalRequest_Entity_Request::getMessageMap($dismissal_request->extra) !== "") {
				continue;
			}

			$to_update_dismissal_request_id_list[] = $dismissal_request->dismissal_request_id;
			$to_update_dismissal_request_list[]    = $dismissal_request;

			$date_from = min($date_from, $dismissal_request->created_at);
		}

		Gateway_Event_Dispatcher::dispatch(Type_Event_DismissalRequest_MessageMapFixRequired::create($to_update_dismissal_request_id_list, $date_from), true);
		return $to_update_dismissal_request_list;
	}

	/**
	 * Выполняет обновления связанной message_map для заявок.
	 *
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws cs_RowNotUpdated
	 * @throws \parseException
	 */
	#[Type_Attribute_EventListener(Type_Event_DismissalRequest_MessageMapFixRequired::EVENT_TYPE, trigger_extra: ["group" => Type_Attribute_EventListener::SLOW_GROUP])]
	public static function update(Struct_Event_DismissalRequest_MessageMapFixRequired $event_data):void {

		// получаем сообщения из диалогов
		$rel_list = Gateway_Socket_Conversation::getDismissalRequestMessageMaps($event_data->dismissal_request_id_list, $event_data->date_from, time());

		foreach ($event_data->dismissal_request_id_list as $dismissal_request_id) {

			// если не нашлось сообщения, то не судьба :(
			if (!isset($rel_list[$dismissal_request_id])) {

				// тут явно что-то нужно сделать,
				// чтобы не дергались лишние запросы
				continue;
			}

			/** начало транзакции */
			Gateway_Db_CompanyData_DismissalRequest::beginTransaction();

			// блокируем на запись, заодно получаем актуальные данные
			$dismissal_request = Domain_DismissalRequest_Entity_Request::getForUpdate($dismissal_request_id);

			$message_map              = $rel_list[$dismissal_request->dismissal_request_id];
			$dismissal_request->extra = Domain_DismissalRequest_Entity_Request::setMessageMap($dismissal_request->extra, $message_map);

			Gateway_Db_CompanyData_DismissalRequest::set($dismissal_request->dismissal_request_id, [
				"extra" => $dismissal_request->extra,
			]);

			/** конец транзакции */
			Gateway_Db_CompanyData_DismissalRequest::commitTransaction();
		}
	}
}
