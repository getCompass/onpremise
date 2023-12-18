<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;

/**
 * Class Apiv1_Company_Notifications
 */
class Apiv1_Notifications extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"doDisable",
		"doEnable",
		"setSnoozedEvent",
		"unsetSnoozedEvent",
	];

	public const MEMBER_ACTIVITY_METHOD_LIST = [
		"doDisable",
		"doEnable",
		"setSnoozedEvent",
		"unsetSnoozedEvent",
	];

	/**
	 * Приостановить получение уведомлений о новых сообщениях на определенное время
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 */
	public function doDisable():array {

		$interval_minutes = $this->post(\Formatter::TYPE_INT, "interval_minutes", 0);

		// блокируем за превышенное число вызовов метода
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::NOTIFICATION_TOGGLE);

		// отключаем уведомления
		try {
			$new_snoozed_until = Domain_Notifications_Scenario_Api::doDisable($this->user_id, $interval_minutes);
		} catch (cs_SnoozeTimeIntervalLessThenMinute) {
			return $this->error(350, "Interval_minutes < 0: " . $interval_minutes);
		} catch (cs_NotificationsSnoozeTimeLimitExceeded $e) {

			return $this->error(351, "Notification shutdown limit exceeded", [
				"snoozed_until" => (int) $e->getMaxTimeLimit(),
			]);
		}

		return $this->ok([
			"snoozed_until" => (int) $new_snoozed_until,
		]);
	}

	/**
	 * Обнуляет таймер отключения уведомлений
	 *
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 */
	public function doEnable():array {

		// блокируем за превышенное число вызовов метода
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::NOTIFICATION_TOGGLE);

		Domain_Notifications_Scenario_Api::doEnable($this->user_id);

		return $this->ok();
	}

	/**
	 * Устанавливаем отключение уведомления с таймером для указанного события
	 *
	 * @throws \blockException
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public function setSnoozedEvent():array {

		$event_type = $this->post(\Formatter::TYPE_INT, "event_type");

		// блокируем за превышенное число вызовов
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::NOTIFICATION_FOREVENTTOGGLE);

		// фиксируем ивент как замьюченный
		try {
			Domain_Notifications_Scenario_Api::setSnoozedEvent($this->user_id, $event_type);
		} catch (cs_IncorrectNotificationToggleData) {
			return $this->error(352, "Unsupported snoozed event type");
		}

		return $this->ok();
	}

	/**
	 * Снимаем отключение уведомления с таймером для указанного события
	 *
	 * @throws \blockException
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public function unsetSnoozedEvent():array {

		$event_type = $this->post(\Formatter::TYPE_INT, "event_type");

		// блокируем за превышенное число вызовов
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::NOTIFICATION_FOREVENTTOGGLE);

		// фиксируем ивент как замьюченный
		try {
			Domain_Notifications_Scenario_Api::unsetSnoozedEvent($this->user_id, $event_type);
		} catch (cs_IncorrectNotificationToggleData) {
			return $this->error(352, "Unsupported snoozed event type");
		}

		return $this->ok();
	}
}