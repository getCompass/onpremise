<?php

namespace Compass\Federation;

/**
 * существующие уровни жесткости блокировки пользователя Compass
 * @package Compass\Federation
 */
enum Domain_Ldap_Entity_UserBlocker_Level: string {

	case LIGHT = "light";
	case HARD = "hard";
}