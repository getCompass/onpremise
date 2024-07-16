<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Апгрейднуть сингл до обычной конференции
 */
class Domain_Jitsi_Action_Conference_UpgradeSingle {

	/**
	 * выполняем действие
	 *
	 * @param Struct_Db_JitsiData_Conference             $conference
	 * @param Struct_Db_JitsiData_UserActiveConference[] $conference_member_list
	 *
	 * @return Struct_Db_JitsiData_Conference
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function do(Struct_Db_JitsiData_Conference $conference, array $conference_member_list):Struct_Db_JitsiData_Conference {

		$conference->data = Domain_Jitsi_Entity_Conference_Data::setConferenceType(
			$conference->data, Domain_Jitsi_Entity_Conference_Data::CONFERENCE_TYPE_DEFAULT);
		$conference->data = Domain_Jitsi_Entity_Conference_Data::setOpponentUserId($conference->data, 0);

		Gateway_Bus_SenderBalancer::activeConferenceUpdated(
			array_column($conference_member_list, "user_id"),
			Gateway_Bus_SenderBalancer_Event_ActiveConferenceUpdated_V1::ACTION_UPDATED_CONFERENCE_DATA,
			Struct_Api_Conference_Data::buildFromDB($conference),
			null,
			null,
			null,
		);
		return Domain_Jitsi_Entity_Conference::setData($conference, $conference->data);
	}
}