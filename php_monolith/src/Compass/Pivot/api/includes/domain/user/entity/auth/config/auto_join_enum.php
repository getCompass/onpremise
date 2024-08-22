<?php

namespace Compass\Pivot;

/** список возможных значений для параметра sso.auto_join_to_team */
enum Domain_User_Entity_Auth_Config_AutoJoinEnum: string {

	case MEMBER = "member";
	case GUEST = "guest";
	case MODERATION = "moderation";
	case DISABLED = "disabled";
}