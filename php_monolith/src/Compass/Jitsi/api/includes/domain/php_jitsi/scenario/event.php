<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс обработки сценариев событий из php_jitsi
 */
class Domain_PhpJitsi_Scenario_Event {

	/**
	 * Нужно проверить состояние сингл звонка jitsi
	 *
	 * @long
	 *
	 * @param Struct_Event_Jitsi_NeedCheckSingleConference $event_data
	 *
	 * @return Type_Task_Struct_Response
	 * @throws ParseFatalException
	 * @throws \cs_CurlError
	 * @throws \parseException
	 */
	#[Type_Task_Attribute_Executor(Type_Event_Jitsi_NeedCheckSingleConference::EVENT_TYPE, Struct_Event_Jitsi_NeedCheckSingleConference::class)]
	public static function onNeedCheckSingleConference(Struct_Event_Jitsi_NeedCheckSingleConference $event_data):Type_Task_Struct_Response {

		// получаем конференцию
		try {
			$conference = Domain_Jitsi_Entity_Conference::get($event_data->conference_id);
		} catch (Domain_Jitsi_Exception_Conference_NotFound) {
			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_DONE);
		}

		// если конференция новая или не сингл, то ничего не делаем
		if (Domain_Jitsi_Entity_Conference_Data::getConferenceType($conference->data) !== Domain_Jitsi_Entity_Conference_Data::CONFERENCE_TYPE_SINGLE
			|| $conference->status === Domain_Jitsi_Entity_Conference::STATUS_FINISHED) {

			return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_DONE);
		}

		// получаем изначального оппонента звонка
		$opponent_user_id = Domain_Jitsi_Entity_Conference_Data::getOpponentUserId($conference->data);

		// проверяем, что в сингл еще никто не зашел
		$conference_member_list = Domain_Jitsi_Entity_ConferenceMember::getConferenceMemberList($event_data->conference_id);

		$opponent_conference_member = null;
		foreach ($conference_member_list as $conference_member) {

			if ($conference_member->status !== Domain_Jitsi_Entity_ConferenceMember_Status::JOINING) {
				return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_DONE);
			}

			if ($opponent_user_id === (int) Domain_Jitsi_Entity_ConferenceMember_MemberId::resolveId($conference_member->member_id)) {
				$opponent_conference_member = $conference_member;
			}
		}

		// если нашли оппонента, игнорируем
		if ($opponent_conference_member !== null) {

			$opponent_conference_member->status = Domain_Jitsi_Entity_ConferenceMember::updateOnIgnored(
				$opponent_conference_member->member_type, $opponent_conference_member->member_id, $conference->conference_id);

			Domain_Pivot_Entity_Event_AddMediaConferenceMessage::create(
				$conference, $opponent_conference_member, Domain_Jitsi_Entity_ConferenceMember_MemberId::resolveId($opponent_conference_member->member_id)
			);

			// отправляем ws, что звонок проигнорировали
			Gateway_Bus_SenderBalancer::conferenceAcceptStatusUpdated(
				$conference->conference_id, $opponent_conference_member->status->getAcceptStatusOutput(), $opponent_user_id,
				[$conference->creator_user_id, $opponent_user_id]);
		}

		// завершаем конференцию
		try {

			Domain_Jitsi_Action_Conference_FinishConference::do($conference->creator_user_id, $conference);
		} catch (Domain_Jitsi_Exception_ConferenceMember_IncorrectMemberId|Domain_Jitsi_Exception_ConferenceMember_NotFound|Domain_Jitsi_Exception_Conference_NotFound
		|Domain_Jitsi_Exception_Node_NotFound|Domain_Jitsi_Exception_Node_RequestFailed) {
			// ничего не делаем, чтобы ивент не крутился бесконечно
		}

		return Type_Task_Struct_Response::build(Type_Task_Handler::DELIVERY_STATUS_DONE);
	}
}