<?php

namespace Compass\Company;

/**
 * Ленивое обновление связанных сообщения для заявок на наем.
 * Появилось в результате обновления заявок и добавления в них поля message_map.
 */
class Domain_HiringRequest_Lazy_UpdateMessageMap {

	/**
	 * Возвращает все заявки, которые нужно обновить.
	 * Обновлять нужно заявки, у которых нет привязанного сообщения.
	 *
	 * @param Struct_Db_CompanyData_HiringRequest[] $hiring_request_list
	 *
	 * @return Struct_Db_CompanyData_HiringRequest[]
	 */
	public static function check(array $hiring_request_list):array {

		$to_update_hiring_request_id_list = [];
		$to_update_hiring_request_list    = [];

		$date_from = time();

		foreach ($hiring_request_list as $hiring_request) {

			if (Domain_DismissalRequest_Entity_Request::getMessageMap($hiring_request->extra) !== "") {
				continue;
			}

			$to_update_hiring_request_id_list[] = $hiring_request->hiring_request_id;
			$to_update_hiring_request_list[]    = $hiring_request;

			$date_from = min($date_from, $hiring_request->created_at);
		}

		Gateway_Event_Dispatcher::dispatch(Type_Event_HiringRequest_MessageMapFixRequired::create($to_update_hiring_request_id_list, $date_from), true);
		return $to_update_hiring_request_list;
	}

	/**
	 * Выполняет обновления связанной message_map для заявок.
	 *
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \parseException
	 */
	#[Type_Attribute_EventListener(Type_Event_HiringRequest_MessageMapFixRequired::EVENT_TYPE, trigger_extra: ["group" => Type_Attribute_EventListener::SLOW_GROUP])]
	public static function update(Struct_Event_HiringRequest_MessageMapFixRequired $event_data):void {

		// получаем сообщения из диалогов
		$rel_list = Gateway_Socket_Conversation::getHiringRequestMessageMaps($event_data->hiring_request_id_list, $event_data->date_from, time());

		foreach ($event_data->hiring_request_id_list as $hiring_request_id) {

			// если не нашлось сообщения, то не судьба :(
			if (!isset($rel_list[$hiring_request_id])) {

				// тут явно что-то нужно сделать,
				// чтобы не дергались лишние запросы
				continue;
			}

			/** начало транзакции */
			Gateway_Db_CompanyData_HiringRequest::beginTransaction();

			try {

				// лочим на запись, заодно получаем актуальные данные
				$hiring_request = Domain_HiringRequest_Entity_Request::getForUpdate($hiring_request_id);
				$message_map    = $rel_list[$hiring_request->hiring_request_id];

				// проверяем, может запись уже обновилась ранее
				if (Domain_HiringRequest_Entity_Request::getMessageMap($hiring_request->extra) !== "") {

					Gateway_Db_CompanyData_HiringRequest::rollback();
					continue;
				}

				Gateway_Db_CompanyData_HiringRequest::set($hiring_request->hiring_request_id, [
					"extra" => Domain_HiringRequest_Entity_Request::setMessageMap($hiring_request->extra, $message_map),
				]);
			} catch (cs_RowNotUpdated|cs_HireRequestNotExist) {

				// запись уже обновилась ранее
				Gateway_Db_CompanyData_HiringRequest::rollback();
				continue;
			}

			Gateway_Db_CompanyData_HiringRequest::commitTransaction();
			/** конец транзакции */
		}
	}
}
