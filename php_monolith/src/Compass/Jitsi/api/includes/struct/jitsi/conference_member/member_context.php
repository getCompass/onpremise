<?php

namespace Compass\Jitsi;

/** структура описывает данные об участнике, который намеревается вступить в конференцию */
class Struct_Jitsi_ConferenceMember_MemberContext {

	public function __construct(
		public Domain_Jitsi_Entity_ConferenceMember_Type $member_type,
		public string|int                                $member_id,
		public string                                    $ip_address,
		public string                                    $user_agent,
		public bool                                      $is_moderator,
		public int                                       $space_id,
	) {
	}
}