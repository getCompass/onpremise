<?php

namespace Compass\Company;

/**
 * Базовый класс для действия для получения настроек уведомлений
 */
class Domain_Notifications_Action_GetPreferences {

	/**
	 * Выполняем action
	 */
	public static function do():array {

		// получаем настройки уведомлений пользователя
		return [
			"is_snoozed"                    => (int) 0,
			"is_snoozed_for_group_messages" => (int) 0,
			"snoozed_until"                 => (int) 0,
		];
	}
}