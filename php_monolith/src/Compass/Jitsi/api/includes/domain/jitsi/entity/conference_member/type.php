<?php

namespace Compass\Jitsi;

/**
 * возможные типы участников конференции
 */
enum Domain_Jitsi_Entity_ConferenceMember_Type: int {

	case COMPASS_USER = 1; // пользователь компасс
	case GUEST = 2; // гость из браузера
}