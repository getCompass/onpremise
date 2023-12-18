<?php

namespace Compass\Company;

/**
 * Базовый класс для действия отклонения заявки на найм нового сотрудника
 */
class Domain_HiringRequest_Action_Reject {

	/**
	 * Выполняем action
	 *
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function do(Struct_Db_CompanyData_HiringRequest $hiring_request):Struct_Db_CompanyData_HiringRequest {

		$old_status = $hiring_request->status;
		return static::_handleReject($hiring_request, $old_status == Domain_HiringRequest_Entity_Request::STATUS_NEED_POSTMODERATION);
	}

	/**
	 * Переводим заявку в статус "отклонено"
	 *
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	protected static function _handleReject(
		Struct_Db_CompanyData_HiringRequest $hiring_request,
		bool                                $is_company_candidate_not_exist
	):Struct_Db_CompanyData_HiringRequest {

		// если кандидат отсутствует в компании
		if ($is_company_candidate_not_exist) {

			[$user_info] = Gateway_Socket_Pivot::getUserInfo($hiring_request->candidate_user_id);

			$hiring_request->extra = Domain_HiringRequest_Entity_Request::setCandidateUserInfo(
				$hiring_request->extra, $user_info->full_name, $user_info->avatar_file_key, $user_info->avatar_color_id
			);
		}

		$hiring_request->updated_at = time();
		Domain_HiringRequest_Entity_Request::reject($hiring_request);

		Domain_Company_Entity_Dynamic::decHiringByStatus($hiring_request->status);

		$hiring_request->status = Domain_HiringRequest_Entity_Request::STATUS_REJECTED;
		Domain_Company_Entity_Dynamic::incHiringByStatus($hiring_request->status);

		// убираем уведомление у всех администраторов от том, что создавалась заявка на найм
		Domain_Member_Action_UndoNotification::do($hiring_request->candidate_user_id, Domain_Member_Entity_Menu::JOIN_REQUEST, $hiring_request->hiring_request_id);

		return $hiring_request;
	}

	/**
	 * получаем форматированную заявку
	 */
	protected static function _getFormattedRequest(
		Struct_Db_CompanyData_HiringRequest $hiring_request
	):Struct_Domain_HiringRequest_Formatted {

		$user_info = Domain_HiringRequest_Action_GetUserInfoFromRequest::do($hiring_request);

		[$formatted_hiring_request] = Domain_HiringRequest_Action_Format::do($hiring_request, $user_info);

		return $formatted_hiring_request;
	}

	/**
	 * пушим событие о статусе заявки участникам чата
	 *
	 * @throws \parseException
	 */
	protected static function _pushRequestToChatMembers(
		Struct_Domain_HiringRequest_Formatted $formatted_hiring_request
	):void {

		Domain_HiringRequest_Entity_Request::sendHiringRequestStatusChangedEvent(
			Apiv1_Format::hiringRequest($formatted_hiring_request));
	}

	/**
	 * пушим событие о статусе заявки в go_event
	 *
	 * @throws \parseException
	 */
	protected static function _pushRequestToGoEvent(
		Struct_Domain_HiringRequest_Formatted $formatted_hiring_request
	):void {

		Gateway_Event_Dispatcher::dispatch(
			Type_Event_HiringRequest_StatusChanged::create(
				Apiv1_Format::hiringRequest($formatted_hiring_request)),
			true);
	}
}