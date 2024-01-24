<?php

namespace Compass\Company;

/**
 * Базовый класс для действия отклонения заявки на найм нового сотрудника
 */
class Domain_HiringRequest_Action_Revoke {

	/**
	 * Выполняем action
	 *
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function do(Struct_Db_CompanyData_HiringRequest $hiring_request, string $candidate_full_name, string $candidate_avatar_file_key, int $candidate_avatar_color_id):Struct_Db_CompanyData_HiringRequest {

		$hiring_request           = static::_handle(
			$hiring_request, $candidate_full_name, $candidate_avatar_file_key, $candidate_avatar_color_id);
		$formatted_hiring_request = static::_getFormattedRequest($hiring_request);

		static::_pushRequestToChatMembers($formatted_hiring_request);
		static::_pushRequestToGoEvent($formatted_hiring_request);
		return $hiring_request;
	}

	/**
	 * переводим заявку в статус "отклонено"
	 *
	 * @throws \parseException
	 * @throws \returnException|\queryException
	 */
	protected static function _handle(Struct_Db_CompanyData_HiringRequest $hiring_request, string $candidate_full_name, string $candidate_avatar_file_key, int $candidate_avatar_color_id):Struct_Db_CompanyData_HiringRequest {

		$hiring_request->updated_at = time();
		$hiring_request             = Domain_HiringRequest_Entity_Request::decline(
			$hiring_request, $candidate_full_name, $candidate_avatar_file_key, $candidate_avatar_color_id);

		Domain_Company_Entity_Dynamic::incHiringByStatus(Domain_HiringRequest_Entity_Request::STATUS_REVOKED);

		Domain_Company_Entity_Dynamic::decHiringByStatus($hiring_request->status);
		$hiring_request->status = Domain_HiringRequest_Entity_Request::STATUS_REVOKED;

		// убираем уведомление у всех администраторов от том, что создавалась заявка на найм
		Domain_Member_Action_UndoNotification::do(
			$hiring_request->candidate_user_id,
			Domain_Member_Entity_Menu::JOIN_REQUEST,
			$hiring_request->hiring_request_id
		);

		return $hiring_request;
	}

	/**
	 * получаем форматированную заявку
	 */
	protected static function _getFormattedRequest(
		Struct_Db_CompanyData_HiringRequest $hiring_request
	):Struct_Domain_HiringRequest_Formatted {

		$user_info = Domain_HiringRequest_Entity_Request::getCandidateUserInfo($hiring_request->extra, $hiring_request->candidate_user_id);
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

		Domain_HiringRequest_Entity_Request::sendHiringRequestStatusChangedEvent(Apiv1_Format::hiringRequest($formatted_hiring_request));
	}

	/**
	 * пушим событие о статусе заявки в go_event
	 *
	 * @throws \parseException
	 */
	protected static function _pushRequestToGoEvent(
		Struct_Domain_HiringRequest_Formatted $formatted_hiring_request
	):void {

		Gateway_Event_Dispatcher::dispatch(Type_Event_HiringRequest_StatusChanged::create(Apiv1_Format::hiringRequest($formatted_hiring_request)), true);
	}
}