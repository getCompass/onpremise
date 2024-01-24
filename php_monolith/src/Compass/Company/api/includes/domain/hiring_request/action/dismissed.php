<?php

namespace Compass\Company;

use CompassApp\Domain\Member\Entity\Member;

/**
 * Базовый класс для действия с заявкой при увольнении
 */
class Domain_HiringRequest_Action_Dismissed {

	/**
	 * Выполняем action
	 *
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function do(int $user_id, Struct_Db_CompanyData_DismissalRequest $dismissal_request):void {

		try {

			// получаем заявки найма и пользователей, для которых нужно найти информацию
			$entry = Gateway_Db_CompanyData_EntryList::getEntryLast($dismissal_request->dismissal_user_id);

			$hiring_request = Domain_HiringRequest_Entity_Request::getByEntryId($entry->entry_id);
			// если пустая заявка - вполне возможно, просто идем дальше
		} catch (\cs_RowIsEmpty) {
			return;
		}

		$member_info = Gateway_Bus_CompanyCache::getMember($dismissal_request->dismissal_user_id);
		$user_info   = new Struct_User_Info(
			$user_id,
			$member_info->full_name,
			$member_info->avatar_file_key,
			\CompassApp\Domain\Member\Entity\Extra::getAvatarColorId($member_info->extra),
		);

		$old_status               = $hiring_request->status;
		$hiring_request           = static::_handleDismissed($hiring_request, $old_status, $user_info);
		$formatted_hiring_request = static::_getFormattedRequest($hiring_request, $user_info);

		static::_pushRequestToChatMembers($formatted_hiring_request);
		static::_pushRequestToGoEvent($formatted_hiring_request);

		// отправляем системные сообщения в тред заявки
		static::_pushSystemMessageToThread($user_id, $dismissal_request->dismissal_user_id, $hiring_request, $old_status);
	}

	/**
	 * переводим заявку в статус "уволен"
	 *
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	protected static function _handleDismissed(Struct_Db_CompanyData_HiringRequest $hiring_request, int $old_status, Struct_User_Info $user_info):Struct_Db_CompanyData_HiringRequest {

		$hiring_request->extra = Domain_HiringRequest_Entity_Request::setCandidateUserInfo(
			$hiring_request->extra, $user_info->full_name, $user_info->avatar_file_key, $user_info->avatar_color_id
		);

		$hiring_request->updated_at = time();
		$hiring_request             = Domain_HiringRequest_Entity_Request::dismiss($hiring_request);

		Domain_Company_Entity_Dynamic::decHiringByStatus($old_status);

		Domain_Company_Entity_Dynamic::incHiringByStatus($hiring_request->status);

		return $hiring_request;
	}

	/**
	 * получаем форматированную заявку
	 */
	protected static function _getFormattedRequest(Struct_Db_CompanyData_HiringRequest $hiring_request, Struct_User_Info $user_info):Struct_Domain_HiringRequest_Formatted {

		[$formatted_hiring_request] = Domain_HiringRequest_Action_Format::do($hiring_request, $user_info);

		return $formatted_hiring_request;
	}

	/**
	 * пушим событие о статусе заявки участникам чата
	 *
	 * @throws \parseException
	 */
	protected static function _pushRequestToChatMembers(Struct_Domain_HiringRequest_Formatted $formatted_hiring_request):void {

		Domain_HiringRequest_Entity_Request::sendHiringRequestStatusChangedEvent(Apiv1_Format::hiringRequest($formatted_hiring_request));
	}

	/**
	 * пушим событие о статусе заявки в go_event
	 *
	 * @throws \parseException
	 */
	protected static function _pushRequestToGoEvent(Struct_Domain_HiringRequest_Formatted $formatted_hiring_request):void {

		Gateway_Event_Dispatcher::dispatch(Type_Event_HiringRequest_StatusChanged::create(Apiv1_Format::hiringRequest($formatted_hiring_request)), true);
	}

	/**
	 * добавляем системное сообщение в тред о смене статуса заявки
	 *
	 * @throws \returnException
	 */
	protected static function _pushSystemMessageToThread(int $user_id, int $dismissal_user_id, Struct_Db_CompanyData_HiringRequest $hiring_request, int $old_status):void {

		Gateway_Socket_Thread::addSystemMessageOnHireRequestStatusChanged(
			Domain_HiringRequest_Entity_Request::getThreadMap($hiring_request->extra),
			Domain_HiringRequest_Entity_Request::HIRING_REQUEST_NAME_TYPE,
			Domain_HiringRequest_Entity_Request::HIRING_REQUEST_TYPE_SCHEMA[$hiring_request->status],
			Domain_HiringRequest_Entity_Request::HIRING_REQUEST_TYPE_SCHEMA[$old_status],
			$user_id,
			$dismissal_user_id
		);
	}
}