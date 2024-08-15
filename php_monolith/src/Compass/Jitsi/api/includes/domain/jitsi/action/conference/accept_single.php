<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Принять сингл звонок
 */
class Domain_Jitsi_Action_Conference_AcceptSingle {

	/**
	 * выполняем действие
	 *
	 * @param Struct_Db_JitsiData_Conference       $conference
	 * @param Struct_Db_JitsiData_ConferenceMember $conference_member
	 *
	 * @return void
	 * @throws ParseFatalException
	 * @throws \busException
	 * @throws \parseException
	 */
	public static function do(Struct_Db_JitsiData_Conference $conference, Struct_Db_JitsiData_ConferenceMember $conference_member):void {

		$opponent_user_id = Domain_Jitsi_Entity_Conference_Data::getOpponentUserId($conference->data);

		// у входящего пользователя должен быть статус dialing
		if ($conference_member->status->getAcceptStatusOutput() !== Domain_Jitsi_Entity_ConferenceMember_Status::ACCEPT_STATUS_DIALING) {
			return;
		}

		$conference_member->status = Domain_Jitsi_Entity_ConferenceMember::updateOnJoin(
			Domain_Jitsi_Entity_ConferenceMember_Type::COMPASS_USER, $conference_member->member_id, $conference->conference_id, $conference_member->data);

		// отправляем вску и сообщение, что приняли звонок
		Domain_Pivot_Entity_Event_AddMediaConferenceMessage::create($conference, $conference_member);

		Gateway_Bus_SenderBalancer::conferenceAcceptStatusUpdated(
			$conference->conference_id,
			$conference_member->status->getAcceptStatusOutput(),
			$opponent_user_id,
			[$opponent_user_id, $conference->creator_user_id]
		);
	}
}