<?php

namespace Compass\Jitsi;

use BaseFrame\Server\ServerProvider;

/**
 * класс для проверки наличия возможности вступить участнику в приватную конференцию
 * @package Compass\Jitsi
 */
class Domain_Jitsi_Entity_ConferenceMember_JoiningAsserts_PrivateConference implements Domain_Jitsi_Entity_ConferenceMember_JoiningAsserts_Interface {

	public static function assert(Struct_Jitsi_ConferenceMember_MemberContext $member_context, Struct_Db_JitsiData_Conference $conference):void {

		// если конференция не приватная, то пропускаем проверки
		if (!$conference->is_private) {
			return;
		}

		// если это гость, то не позволяем ему присоединяться в приватную конференцию
		if ($member_context->member_type === Domain_Jitsi_Entity_ConferenceMember_Type::GUEST) {
			throw new Domain_Jitsi_Exception_ConferenceMember_AttemptJoinToPrivateConference();
		}

		// если это пользователь Compass
		if ($member_context->member_type === Domain_Jitsi_Entity_ConferenceMember_Type::COMPASS_USER) {

			// если это saas окружение, то проверяем что присоединяющийся пользователь имеет общую компанию с создателем конференции
			if (ServerProvider::isSaas()) {

				$user_id = Domain_Jitsi_Entity_ConferenceMember_MemberId::resolveId($member_context->member_id);
				if (!Domain_User_Entity_SpaceMember::usersHaveIntersectSpace($conference->creator_user_id, $user_id)) {

					throw new Domain_Jitsi_Exception_ConferenceMember_AttemptJoinToPrivateConference();
				}
			}
		}
	}
}