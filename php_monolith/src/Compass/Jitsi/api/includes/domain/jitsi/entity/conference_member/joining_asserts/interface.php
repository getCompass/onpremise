<?php

namespace Compass\Jitsi;

/**
 * Интерфейс описывающий каждый класс с помощью которого происходит проверка возможности участника вступить в конференцию
 */
interface Domain_Jitsi_Entity_ConferenceMember_JoiningAsserts_Interface {

	/**
	 * непосредственно проверка
	 */
	public static function assert(Struct_Jitsi_ConferenceMember_MemberContext $member_context, Struct_Db_JitsiData_Conference $conference):void;
}