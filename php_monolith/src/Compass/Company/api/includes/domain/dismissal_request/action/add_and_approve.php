<?php

namespace Compass\Company;

/**
 * Базовый класс для действия добавления и одобрения заявки на увольнение сотрудника
 */
class Domain_DismissalRequest_Action_AddAndApprove {

	/**
	 * Выполняем action
	 *
	 * @return array
	 * @throws cs_DismissalRequestIsAlreadyExist
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function do(int $creator_user_id, int $dismissal_user_id):array {

		// добавляем $dismissal_user_id в кэш memCache. Если в кэше нету $dismissal_user_id, значит можно добавлять заявку на увольнение
		// это защита от одновременного добавления с разных устройств нескольких заявок на увольнение на один $dismissal_user_id
		Domain_DismissalRequest_Entity_Request::addDismissalRequestUserIdInCache($dismissal_user_id);

		// создаём одобренную заявку
		$dismissal_request = Domain_DismissalRequest_Entity_Request::addApproved($creator_user_id, $dismissal_user_id);

		// обновляем dynamic
		Domain_Company_Entity_Dynamic::inc(Domain_Company_Entity_Dynamic::DISMISSAL_REQUEST_APPROVED);

		// отправляем соообщение заявки о увольнении в диалог
		[$hiring_conversation_map, $dismissal_request_message_map, $dismissal_request_thread_map] =
			Gateway_Socket_Conversation::addDismissalRequestMessage($creator_user_id, $dismissal_request->dismissal_request_id, $dismissal_user_id);

		// сохраняем в заявке мапу сообщения из чата и треда к заявке
		$dismissal_request = Domain_DismissalRequest_Action_SetMessageMap::do($dismissal_request->dismissal_request_id, $dismissal_request_message_map);
		$dismissal_request = Domain_DismissalRequest_Action_SetThreadMap::do($dismissal_request->dismissal_request_id, $dismissal_request_thread_map);

		// отправляем в go_event событие об изменении статуса заявки
		static::_pushRequestToGoEvent($dismissal_request);

		// отправляем системные сообщения в тред к заявке
		static::_pushSystemMessageToThread($dismissal_request);

		// удаляем из кэша dismissal_user_id, чтобы можно было уволить пользователя в будущем
		// снимаем защиту от одновременного добавления с разных устройств нескольких заявок на увольнение на один dismissal_user_id
		Domain_DismissalRequest_Entity_Request::deleteDismissalRequestUserIdFromCache($dismissal_request->dismissal_user_id);

		return [$dismissal_request, $hiring_conversation_map];
	}

	/**
	 * шлем эвент об изменении статуса заявки в go_event
	 *
	 * @throws \parseException
	 */
	protected static function _pushRequestToGoEvent(Struct_Db_CompanyData_DismissalRequest $dismissal_request):void {

		Gateway_Event_Dispatcher::dispatch(Type_Event_DismissalRequest_StatusChanged::create(Apiv1_Format::dismissalRequest($dismissal_request)), true);
	}

	/**
	 * добавляем в тред заявки системное сообщение
	 *
	 * @throws \returnException
	 */
	protected static function _pushSystemMessageToThread(Struct_Db_CompanyData_DismissalRequest $dismissal_request):void {

		Gateway_Socket_Thread::addSystemMessageToDismissalRequestThread(
			$dismissal_request->creator_user_id,
			$dismissal_request->dismissal_user_id,
			Domain_DismissalRequest_Entity_Request::getThreadMap($dismissal_request->extra),
		);
	}
}