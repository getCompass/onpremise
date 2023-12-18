<?php

declare(strict_types = 1);

namespace Compass\Speaker;

/**
 * Агрегатор подписок на событие для домена компании.
 */
class Domain_Company_Scenario_Event {

	/**
	 * Callback для события - пользователь разлогинился из компании
	 */
	#[Type_Attribute_EventListener(Type_Event_UserCompany_UserLogoutCompany::EVENT_TYPE)]
	public static function onUserLogoutCompany(Struct_Event_UserCompany_UserLogoutCompany $event_data):void {

		// раньше завершали звонок после разлогина, теперь ничего не делаем
	}
}
