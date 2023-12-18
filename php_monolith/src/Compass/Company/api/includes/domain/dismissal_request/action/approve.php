<?php

namespace Compass\Company;

/**
 * Базовый класс для действия одобрения заявки на увольнение сотрудника
 */
class Domain_DismissalRequest_Action_Approve {

	/**
	 * Выполняем action
	 *
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function do(int $user_id, Struct_Db_CompanyData_DismissalRequest $dismissal_request):Struct_Db_CompanyData_DismissalRequest {

		// удаляем из кэша dismissal_user_id, чтобы можно было уволить пользователя в будущем
		// снимаем защиту от одновременного добавления с разных устройств нескольких заявок на увольнение на один dismissal_user_id
		Domain_DismissalRequest_Entity_Request::deleteDismissalRequestUserIdFromCache($dismissal_request->dismissal_user_id);

		$old_status        = $dismissal_request->status;
		$dismissal_request = static::_handleApprove($dismissal_request);
		static::_pushRequestToChatMembers($dismissal_request);
		static::_pushRequestToGoEvent($dismissal_request);
		static::_pushSystemMessageToThread($user_id, $dismissal_request, $old_status);

		return $dismissal_request;
	}

	/**
	 * переводим заявку в статус "одобрено"
	 *
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	protected static function _handleApprove(Struct_Db_CompanyData_DismissalRequest $dismissal_request):Struct_Db_CompanyData_DismissalRequest {

		$dismissal_request->status     = Domain_DismissalRequest_Entity_Request::STATUS_APPROVED;
		$dismissal_request->updated_at = time();

		// переводим заявку в статус «одобрено»
		Domain_DismissalRequest_Entity_Request::approve($dismissal_request);

		// обновляем dynamic
		Domain_Company_Entity_Dynamic::inc(Domain_Company_Entity_Dynamic::DISMISSAL_REQUEST_APPROVED);
		Domain_Company_Entity_Dynamic::dec(Domain_Company_Entity_Dynamic::DISMISSAL_REQUEST_WAITING);

		return $dismissal_request;
	}

	/**
	 * пушим событие о статусе заявки участникам чата
	 *
	 * @throws \parseException
	 */
	protected static function _pushRequestToChatMembers(Struct_Db_CompanyData_DismissalRequest $dismissal_request):void {

		Domain_DismissalRequest_Entity_Request::sendDismissalRequestStatusChangedEvent(Apiv1_Format::dismissalRequest($dismissal_request));
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
	protected static function _pushSystemMessageToThread(int $user_id, Struct_Db_CompanyData_DismissalRequest $dismissal_request, int $old_status):void {

		Gateway_Socket_Thread::addSystemMessageOnHireRequestStatusChanged(
			Domain_DismissalRequest_Entity_Request::getThreadMap($dismissal_request->extra),
			Domain_DismissalRequest_Entity_Request::DISMISSAL_REQUEST_NAME_TYPE,
			Domain_DismissalRequest_Entity_Request::DISMISSAL_REQUEST_TYPE_SCHEMA[$dismissal_request->status],
			Domain_DismissalRequest_Entity_Request::DISMISSAL_REQUEST_TYPE_SCHEMA[$old_status],
			$user_id,
			$dismissal_request->dismissal_user_id
		);
	}
}