<?php

namespace Compass\Jitsi;

/**
 * класс для формирования события о отправке сообщения
 */
class Domain_Pivot_Entity_Event_AddMediaConferenceMessage extends Domain_Pivot_Entity_Event_Abstract {

	protected const _EVENT_TYPE = "jitsi.add_media_conference_message";

	/**
	 * Создаем событие
	 *
	 * @throws \busException
	 */
	public static function create(Struct_Db_JitsiData_Conference $conference, Struct_Db_JitsiData_ConferenceMember $member):void {

		$params = [
			"space_id"         => $conference->space_id,
			"user_id"          => $conference->creator_user_id,
			"conference_id"    => $conference->conference_id,
			"link"             => Domain_Jitsi_Entity_ConferenceLink_Main::getHandlerProvider()::getByConference($conference)::prepareLandingConferenceLink($conference),
			"conversation_map" => Domain_Jitsi_Entity_Conference_Data::getConversationMap($conference->data),
			"accept_status"    => $member->status->getAcceptStatusOutput(),
			"conference_code"  => Domain_Jitsi_Entity_Conference::getConferenceCode($conference)
		];

		// отправляем в пивот
		static::_sendToPivot($params);
	}

}