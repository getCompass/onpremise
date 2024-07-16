<?php

namespace Compass\Jitsi;

/**
 * класс для проверки, что конференция имеет подходящее состояние для вступления в нее нового участника
 * @package Compass\Jitsi
 */
class Domain_Jitsi_Entity_ConferenceMember_JoiningAsserts_ConferenceState implements Domain_Jitsi_Entity_ConferenceMember_JoiningAsserts_Interface {

	public static function assert(Struct_Jitsi_ConferenceMember_MemberContext $member_context, Struct_Db_JitsiData_Conference $conference):void {

		// проверяем статус
		if ($conference->status === Domain_Jitsi_Entity_Conference::STATUS_FINISHED) {
			throw new Domain_Jitsi_Exception_Conference_IsFinished();
		}
	}
}