<?php

namespace Compass\Jitsi;

/**
 * класс описывающий поведение гостя в конференции
 * @package Compass\Jitsi
 */
class Domain_Jitsi_Entity_ConferenceMember_Behavior_Guest implements Domain_Jitsi_Entity_ConferenceMember_Behavior_Interface {

	/**
	 * обработка события о присоединении в конференцию
	 */
	public function onJoinConference(string $conference_id, string $member_id):void {

		// обновляем статус участника конференции
		Domain_Jitsi_Entity_ConferenceMember::updateOnJoin(Domain_Jitsi_Entity_ConferenceMember_Type::GUEST, $member_id, $conference_id);
	}

	/**
	 * обработка события о покидании конференции
	 */
	public function onLeftConference(string $conference_id, string $member_id, bool $is_lost_connections):void {

		// обновляем статус участника конференции
		Domain_Jitsi_Entity_ConferenceMember::updateOnLeft(Domain_Jitsi_Entity_ConferenceMember_Type::GUEST, $member_id, $conference_id, true);
	}

	/**
	 * обработка события о выдаче прав модератора
	 */
	public function onConferenceModeratorRightsGranted(string $conference_id, string $member_id):void {

		// обновляем статус модератора
		Domain_Jitsi_Entity_ConferenceMember::updateIsModerator(Domain_Jitsi_Entity_ConferenceMember_Type::GUEST, $member_id, $conference_id, true);
	}
}