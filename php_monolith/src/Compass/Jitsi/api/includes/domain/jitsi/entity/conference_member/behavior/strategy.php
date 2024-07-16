<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс опреедляющий класс стратегии поведения в зависимости от типа участника
 * @package Compass\Jitsi
 */
class Domain_Jitsi_Entity_ConferenceMember_Behavior_Strategy {

	/**
	 * получаем конкретный класс стратегии в зависимости от типа учасника
	 *
	 * @return Domain_Jitsi_Entity_ConferenceMember_Behavior_Interface
	 */
	public static function get(Domain_Jitsi_Entity_ConferenceMember_Type $member_type):Domain_Jitsi_Entity_ConferenceMember_Behavior_Interface {

		return match ($member_type) {
			Domain_Jitsi_Entity_ConferenceMember_Type::GUEST        => new Domain_Jitsi_Entity_ConferenceMember_Behavior_Guest(),
			Domain_Jitsi_Entity_ConferenceMember_Type::COMPASS_USER => new Domain_Jitsi_Entity_ConferenceMember_Behavior_CompassUser(),
			default                                                 => throw new ParseFatalException("unexpected member type")
		};
	}
}