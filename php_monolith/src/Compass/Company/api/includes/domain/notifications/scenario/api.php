<?php

namespace Compass\Company;

use AnalyticUtils\Domain\Event\Entity\User;
use AnalyticUtils\Domain\Event\Entity\Main;
use BaseFrame\Exception\Request\ParamException;

/**
 * Сценарии уведомлений для API
 *
 * Class Domain_Notifications_Scenario_Api
 */
class Domain_Notifications_Scenario_Api {

	/**
	 * Сценарий отключения уведомлений
	 *
	 * @throws \busException
	 * @throws cs_NotificationsSnoozeTimeLimitExceeded
	 * @throws cs_SnoozeTimeIntervalLessThenMinute
	 * @throws paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function doDisable(int $user_id, int $interval_minutes):int {

		Domain_Notifications_Entity_UserNotification_Main::assertValidTimeInterval($interval_minutes);

		$new_snoozed_until = Domain_Notifications_Action_DoDisable::do($user_id, $interval_minutes);

		return $new_snoozed_until;
	}

	/**
	 * Сценарий включения уведомлений
	 *
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function doEnable(int $user_id):void {

		Domain_Notifications_Action_DoEnable::do($user_id);
	}

	/**
	 * Сценарий выключения уведомлений для определенного типа ивента
	 *
	 * @throws \busException
	 * @throws cs_IncorrectNotificationToggleData
	 * @throws paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function setSnoozedEvent(int $user_id, int $event_type):void {

		Domain_Notifications_Action_SetSnoozedEvent::do($user_id, $event_type, true);
	}

	/**
	 * Сценарий включения уведомлений для определенного типа ивента
	 *
	 * @throws \busException
	 * @throws cs_IncorrectNotificationToggleData
	 * @throws paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function unsetSnoozedEvent(int $user_id, int $event_type):void {

		Domain_Notifications_Action_SetSnoozedEvent::do($user_id, $event_type, false);
	}

	/**
	 * Добавляет текущее устройство в список известных для отправки пуш-уведомлений.
	 */
	public static function addDevice(int $user_id, string $device_id):void {

		try {

			// электрон не работает с пушами, рассылаемыми через пушер, поэтому для него ничего не делаем
			if (Type_Api_Platform::getPlatform() === Type_Api_Platform::PLATFORM_ELECTRON) {
				throw new \BaseFrame\Exception\Request\ParamException("unsupported platform");
			}
		} catch (cs_PlatformNotFound) {
			throw new \BaseFrame\Exception\Request\ParamException("unknown platform");
		}

		if ($device_id === "") {
			throw new \BaseFrame\Exception\Request\ParamException("bad device id");
		}

		Domain_User_Action_Notifications_AddDevice::do($user_id, $device_id);
	}
}