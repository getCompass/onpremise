<?php

namespace Compass\Pivot;

/**
 * Class Type_User_Notifications_UserExtra
 */
class Type_User_Notifications_UserExtra {

	// текущая версия extra
	protected const _EXTRA_VERSION = 2;

	// массив с версиями extra
	protected const _EXTRA_SCHEMA = [

		1 => [

			// побитовая маска с логикой на какие эвенты включены уведомления
			"event_mask"  => EVENT_TYPE_ALL_MASK_V2,

			// побитовая маска с логикой отключения уведомлений на определенное время
			"snooze_mask" => 0,

		],
		2 => [

			// побитовая маска с логикой на какие эвенты включены уведомления
			"event_mask"  => EVENT_TYPE_ALL_MASK,

			// побитовая маска с логикой отключения уведомлений на определенное время
			"snooze_mask" => 0,

		],

	];

	/**
	 * возвращает текущую структуру extra с default значениями
	 *
	 */
	public static function initExtra():array {

		return [
			"handler_version" => self::_EXTRA_VERSION,
			"extra"           => self::_EXTRA_SCHEMA[self::_EXTRA_VERSION],
		];
	}

	/**
	 * включаем/отключаем уведомления для нужного эвента
	 *
	 */
	public static function setEventMask(bool $is_enabled, int $event_type, array $extra):array {

		// получаем актуальное extra
		$extra = self::_getExtra($extra);

		// включаем/отключаем уведомления для нужного эвента
		if ($is_enabled) {
			$extra["extra"]["event_mask"] |= $event_type;
		} else {
			$extra["extra"]["event_mask"] &= ~$event_type;
		}

		return $extra;
	}

	/**
	 * получаем event mask
	 *
	 */
	public static function getEventMask(array $extra):int {

		// получаем актуальное extra
		$extra = self::_getExtra($extra);

		return $extra["extra"]["event_mask"];
	}

	/**
	 * получаем snooze_mask
	 *
	 */
	public static function getSnoozeMask(array $extra):int {

		// получаем актуальное extra
		$extra = self::_getExtra($extra);

		return $extra["extra"]["snooze_mask"];
	}

	/**
	 * переключаем временный мут для определенных типов событий
	 *
	 */
	public static function prepareSnoozeMask(int $event_mask, int $is_snoozed, array $extra):array {

		// получаем актуальное extra
		$extra = self::_getExtra($extra);

		// включаем все события, если маска включенных соответвует муту всего
		// т.е. если все было замьючено, то мы должны все размьютить и замьютить конкретный ивент
		if ($extra["extra"]["snooze_mask"] === 0) {
			$extra["extra"]["snooze_mask"] = EVENT_TYPE_ALL_MASK;
		}

		if ($is_snoozed) {
			$extra["extra"]["snooze_mask"] &= ~$event_mask;
		} else {
			$extra["extra"]["snooze_mask"] |= $event_mask;
		}

		// отключаем все события, если маска включенных стала равна всем событиям
		// т.е. все стало размьюченным (все галки "замьютить" сняты)
		if ($extra["extra"]["snooze_mask"] === EVENT_TYPE_ALL_MASK) {
			$extra["extra"]["snooze_mask"] = 0;
		}

		return $extra;
	}

	/**
	 * актуализирует структуру extra
	 *
	 */
	protected static function _getExtra(array $extra):array {

		// сравниваем версию пришедшей extra с текущей
		if ($extra["handler_version"] != self::_EXTRA_VERSION) {

			if ($extra["handler_version"] < 2) {

				// нужно дорисовать недостающий бит с маской уведомлений
				$extra["extra"]["event_mask"] |= EVENT_TYPE_MEMBER_NOTIFICATION_MASK;
			}

			// сливаем текущую версию extra и ту, что пришла
			$extra["extra"]           = array_merge(self::_EXTRA_SCHEMA[self::_EXTRA_VERSION], $extra["extra"]);
			$extra["handler_version"] = self::_EXTRA_VERSION;
		}
		return $extra;
	}
}
