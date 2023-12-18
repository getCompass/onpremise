<?php

namespace Compass\Company;

/**
 * Базовый класс для действия отклонения заявки на увольнение сотрудника
 */
class Domain_DismissalRequest_Action_Reject {

	/**
	 * Выполняем action
	 *
	 * @throws \parseException
	 * @throws \returnException|\queryException
	 */
	public static function do(int $user_id, Struct_Db_CompanyData_DismissalRequest $dismissal_request):Struct_Db_CompanyData_DismissalRequest {

		// считаем dynamic
		$new_status = Domain_DismissalRequest_Entity_Request::STATUS_REJECTED;
		Domain_Company_Entity_Dynamic::incDismissalByStatus($new_status);
		Domain_Company_Entity_Dynamic::decDismissalByStatus($dismissal_request->status);

		$dismissal_request->status     = $new_status;
		$dismissal_request->updated_at = time();

		// переводим заявку в статус «отклонено»
		Domain_DismissalRequest_Entity_Request::reject($dismissal_request);

		// удаляем из кэша dismissal_user_id, чтобы можно было уволить пользователя в будущем
		// снимаем защиту от одновременного добавления с разных устройств нескольких заявок на увольнение на один dismissal_user_id
		Domain_DismissalRequest_Entity_Request::deleteDismissalRequestUserIdFromCache($dismissal_request->dismissal_user_id);

		// шлем эвент об изменении статуса заявки пользователям чата
		Domain_DismissalRequest_Entity_Request::sendDismissalRequestStatusChangedEvent(Apiv1_Format::dismissalRequest($dismissal_request));

		// шлем эвент об изменении статуса заявки в go_event
		Gateway_Event_Dispatcher::dispatch(Type_Event_DismissalRequest_StatusChanged::create(Apiv1_Format::dismissalRequest($dismissal_request)), true);

		// добавляем системное сообщение в тред заявки
		Gateway_Socket_Thread::addSystemMessageOnHireRequestStatusChanged(
			Domain_DismissalRequest_Entity_Request::getThreadMap($dismissal_request->extra),
			Domain_DismissalRequest_Entity_Request::DISMISSAL_REQUEST_NAME_TYPE,
			Domain_DismissalRequest_Entity_Request::DISMISSAL_REQUEST_TYPE_SCHEMA[$dismissal_request->status],
			Domain_DismissalRequest_Entity_Request::DISMISSAL_REQUEST_TYPE_SCHEMA[Domain_DismissalRequest_Entity_Request::STATUS_WAITING],
			$user_id
		);

		return $dismissal_request;
	}
}