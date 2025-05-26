<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * контроллер для технических методов клиента
 */
class Apiv1_Notifications extends \BaseFrame\Controller\Api {

	// поддерживаемые методы. регистр не имеет значение
	public const ALLOW_METHODS = [
		"addToken",
		"doDisable",
		"doDisableForEvent",
		"doEnable",
		"doEnableForEvent",
		"getCurrentSoundType",
		"getPreferences",
		"setSnoozedEvent",
		"setSoundType",
		"unsetSnoozedEvent",
		"confirmPushReceiving",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * метод для добавления токена в базу
	 */
	public function addToken():array {

		$token      = $this->post("?s", "token");
		$token_type = $this->post("?i", "token_type", Type_User_Notifications::TOKEN_TYPE_FIREBASE_LEGACY);

		// заблокирован ли пользователь по превышенному числу вызовов
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::NOTIFICATION_ADDTOKEN);

		// добавляем токен
		try {
			Domain_User_Action_Notifications_AddToken::do($this->user_id, $token, $token_type);
		} catch (cs_NotificationsUnsupportedTokenType) {
			return $this->error(349, "Unsupported token type");
		} catch (cs_NotificationsInvalidToken) {
			return $this->error(349, "Invalid token");
		}

		return $this->ok();
	}

	/**
	 * приостановить получение уведомлений о новых сообщениях на определенное время
	 */
	public function doDisable():array {

		$interval_minutes = $this->post("?i", "interval_minutes");

		// блокируем за превышенное число вызовов метода
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::NOTIFICATION_TOGGLE);

		// отключаем уведомления
		try {
			$new_snoozed_until = Domain_User_Action_Notifications_DoDisable::do($this->user_id, $interval_minutes);
		} catch (cs_NotificationsIntervalLessThenMinute) {
			return $this->error(350, "Interval_minutes < 1: " . $interval_minutes);
		} catch (cs_NotificationsShutdownLimitExceeded $e) {

			return $this->error(351, "Notification shutdown limit exceeded", [
				"snoozed_until" => (int) $e->getMaxTimeLimit(),
			]);
		}

		return $this->ok([
			"snoozed_until" => (int) $new_snoozed_until,
		]);
	}

	/**
	 * Выключает уведомления для определенного вида событий
	 */
	public function doDisableForEvent():array {

		// получаем параметры из post_data
		$event_type = $this->post("?i", "event_type");

		// блокируем за превышенное число вызовов
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::NOTIFICATION_FOREVENTTOGGLE);

		// добавляем токен
		try {
			Domain_User_Action_Notifications_DoDisableForEvent::do($this->user_id, $event_type);
		} catch (cs_IncorrectNotificationToggleData) {
			return $this->error(352, "Unsupported event_type");
		}

		return $this->ok();
	}

	/**
	 * обнуляет таймер отключения уведомлений
	 */
	public function doEnable():array {

		// блокируем за превышенное число вызовов метода
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::NOTIFICATION_TOGGLE);

		Domain_User_Action_Notifications_DoEnable::do($this->user_id);

		return $this->ok();
	}

	/**
	 * включает уведомления для определенного вида событий
	 */
	public function doEnableForEvent():array {

		$event_type = $this->post("?i", "event_type");

		// блокируем за превышенное число вызовов
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::NOTIFICATION_FOREVENTTOGGLE);

		// включаем уведомления в зависимости от типа
		try {
			Domain_User_Action_Notifications_DoEnableForEvent::do($this->user_id, $event_type);
		} catch (cs_IncorrectNotificationToggleData) {
			return $this->error(352, "Unsupported event_type");
		}

		return $this->ok();
	}

	/**
	 * получаем текущий тип звуковых файлов
	 */
	public function getCurrentSoundType():array {

		// получаем текущий тип звуковых файлов
		$sound_type = Domain_User_Action_Notifications_GetCurrentSoundType::do();

		return $this->ok([
			"sound_type" => (int) $sound_type,
		]);
	}

	/**
	 * получаем информацию о состоянии уведомлений в приложении
	 */
	public function getPreferences():array {

		// получаем информацию о состоянии уведомлений в приложении
		$output = Domain_User_Action_Notifications_GetPreferences::do($this->user_id);

		return $this->ok($output);
	}

	/**
	 * устанавливаем отключение уведомления с таймером для указанного события
	 */
	public function setSnoozedEvent():array {

		$event_type = $this->post("?i", "event_type");

		// блокируем за превышенное число вызовов
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::NOTIFICATION_FOREVENTTOGGLE);

		// фиксируем ивент как замьюченный
		try {
			Domain_User_Action_Notifications_SetSnoozedEvent::do($this->user_id, $event_type, true);
		} catch (cs_IncorrectNotificationToggleData) {
			return $this->error(352, "Unsupported snoozed event type");
		}

		return $this->ok();
	}

	/**
	 * устанавливаем тип звуковых файлов
	 */
	public function setSoundType():array {

		$sound_type = $this->post("?i", "sound_type");

		// устанавливаем тип звуков
		try {
			Domain_User_Action_Notifications_SetSoundType::do($sound_type);
		} catch (cs_UserNotHaveToken) {
			return $this->error(349, "User not have token");
		} catch (cs_NotificationUnsupportedSoundType) {
			return $this->error(353, "Unsupported sound type");
		}

		return $this->ok();
	}

	/**
	 * снимаем отключение уведомления с таймером для указанного события
	 */
	public function unsetSnoozedEvent():array {

		$event_type = $this->post("?i", "event_type");

		// блокируем за превышенное число вызовов
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::NOTIFICATION_FOREVENTTOGGLE);

		// фиксируем ивент как замьюченный
		try {
			Domain_User_Action_Notifications_UnsetSnoozedEvent::do($this->user_id, $event_type, false);
		} catch (cs_IncorrectNotificationToggleData) {
			return $this->error(352, "Unsupported snoozed event type");
		}

		return $this->ok();
	}

	/**
	 * Подтверждаем получение пушей
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function confirmPushReceiving():array {

		$uuid_list = $this->post(\Formatter::TYPE_ARRAY, "uuid_list");

		// на онпремайзе нет коллектора, так что разворачиваем
		if (ServerProvider::isOnPremise()) {
			return $this->ok();
		}

		Domain_Notifications_Scenario_Api::confirmPushReceiving($uuid_list);

		return $this->ok();
	}
}