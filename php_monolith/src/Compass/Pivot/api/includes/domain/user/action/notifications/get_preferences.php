<?php

namespace Compass\Pivot;

/**
 * Получаем информацию о состоянии уведомлений в приложении
 */
class Domain_User_Action_Notifications_GetPreferences {

	/**
	 * Получаем информацию о состоянии уведомлений в приложении
	 *
	 */
	public static function do(int $user_id):array {

		// получаем состояние уведомлений пользователя
		$notification_preferences = Type_User_Notifications::getPreferences($user_id);

		// форматируем ответ для frontend
		return self::_formatNotificationPreferences($notification_preferences);
	}

	/**
	 * форматируем ответ для frontend
	 *
	 * @return int[]
	 */
	protected static function _formatNotificationPreferences(array $notification_preferences):array {

		// генерируем ответ на фронтенд
		$output = [
			"is_snoozed"                    => 0,
			"snoozed_until"                 => 0,
			"is_snoozed_for_group_messages" => (int) $notification_preferences["is_snoozed_for_group_messages"],
			"is_enabled_for_messages"       => (int) $notification_preferences["is_enabled_for_messages"],
			"is_enabled_for_threads"        => (int) $notification_preferences["is_enabled_for_threads"],
			"is_enabled_for_invites"        => (int) $notification_preferences["is_enabled_for_invites"],
			"has_notification_token"        => (int) $notification_preferences["has_notification_token"],
			"has_voip_token"                => (int) $notification_preferences["has_voip_token"],
		];

		// если приостановлено получение уведомлений
		if ($notification_preferences["snoozed_until"] > time()) {

			// добавляем в ответ поле snoozed_until
			$output["is_snoozed"]    = 1;
			$output["snoozed_until"] = (int) $notification_preferences["snoozed_until"];
		}

		return $output;
	}
}