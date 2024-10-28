<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс с обработчиками событий в jitsi
 * @package Compass\Jitsi
 */
class Domain_Jitsi_Scenario_Event {

	/**
	 * При завершении конференции
	 *
	 * @throws \parseException
	 * @throws ParseFatalException
	 */
	public static function onConferenceFinished(string $conference_id):void {

		try {
			$conference                  = Domain_Jitsi_Entity_Conference::get($conference_id);
			$user_active_conference_list = Domain_Jitsi_Entity_UserActiveConference::getByActiveConferenceId($conference_id);
		} catch (Domain_Jitsi_Exception_Conference_NotFound) {
			return;
		}

		// определяем статус, который установим конференции
		$conference->status = Domain_Jitsi_Entity_Conference::resolveStatusOnFinish($conference);
		Domain_Jitsi_Entity_Conference::updateStatus($conference_id, $conference->status);

		$ws_user_id_list = array_column($user_active_conference_list, "user_id");

		// если сингл звонок, то ws active_conference_updated не передаём создателю звонка
		if (Domain_Jitsi_Entity_Conference_Data::getConferenceType($conference->data) == Domain_Jitsi_Entity_Conference_Data::CONFERENCE_TYPE_SINGLE) {
			$ws_user_id_list = array_diff($ws_user_id_list, [$conference->creator_user_id]);
		}

		// отправляем событие
		Gateway_Bus_SenderBalancer::activeConferenceUpdated(
			$ws_user_id_list,
			Gateway_Bus_SenderBalancer_Event_ActiveConferenceUpdated_V1::ACTION_UPDATED_CONFERENCE_DATA,
			Struct_Api_Conference_Data::buildFromDB($conference),
			null,
			null,
			null,
		);

		// убираем активную конференцию для всех участников, чтобы ни у кого не повисла активная конференция
		Domain_Jitsi_Entity_UserActiveConference::onConferenceFinished($conference_id);
	}

	/**
	 * при старте конференции
	 *
	 * @throws ParseFatalException
	 */
	public static function onConferenceStarted(string $conference_id):void {

		// обновляем статус конференции
		Domain_Jitsi_Entity_Conference::updateStatus($conference_id, Domain_Jitsi_Entity_Conference::STATUS_WAITING);
	}

	/**
	 * при подключении участника в конференцию
	 *
	 * @param string $conference_id
	 * @param string $member_id
	 *
	 * @throws Domain_Jitsi_Exception_ConferenceMember_IncorrectMemberId
	 * @throws ParseFatalException
	 * @long
	 */
	public static function onConferenceMemberJoined(string $conference_id, string $member_id):void {

		$member_type      = Domain_Jitsi_Entity_ConferenceMember_MemberId::resolveMemberType($member_id);
		$member_behaviour = Domain_Jitsi_Entity_ConferenceMember_Behavior_Strategy::get($member_type);
		$member_behaviour->onJoinConference($conference_id, $member_id);
	}

	/**
	 * при покидании конференции участником
	 *
	 * @param string $conference_id
	 * @param string $member_id
	 *
	 * @throws ParseFatalException
	 * @throws Domain_Jitsi_Exception_ConferenceMember_IncorrectMemberId
	 */
	public static function onConferenceMemberLeft(string $conference_id, string $member_id, bool $is_lost_connections = false):void {

		$member_type      = Domain_Jitsi_Entity_ConferenceMember_MemberId::resolveMemberType($member_id);
		$member_behaviour = Domain_Jitsi_Entity_ConferenceMember_Behavior_Strategy::get($member_type);
		$member_behaviour->onLeftConference($conference_id, $member_id, $is_lost_connections);
	}

	/**
	 * при выдаче прав модератора участнику конференции
	 *
	 * @param string $conference_id
	 * @param string $member_id
	 *
	 * @throws ParseFatalException
	 * @throws Domain_Jitsi_Exception_ConferenceMember_IncorrectMemberId
	 */
	public static function onConferenceMemberModeratorRightsGranted(string $conference_id, string $member_id):void {

		$member_type      = Domain_Jitsi_Entity_ConferenceMember_MemberId::resolveMemberType($member_id);
		$member_behaviour = Domain_Jitsi_Entity_ConferenceMember_Behavior_Strategy::get($member_type);
		$member_behaviour->onConferenceModeratorRightsGranted($conference_id, $member_id);
	}
}