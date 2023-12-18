<?php

namespace Compass\Company;

/**
 * Класс для взаимодействия с extra полем настроек уведомлений пользователя
 */
class Domain_Notifications_Entity_UserNotification_Extra {

	public const USER_NOTIFICATION_IS_SNOOZED     = true;  // уведомления ВЫКЛючены
	public const USER_NOTIFICATION_IS_NOT_SNOOZED = false; // уведомления ВКЛючены

	// текущая версия extra
	protected const _EXTRA_VERSION = 2;

	// массив с версиями extra
	protected const _EXTRA_SCHEMA = [

		1 => [

			// побитовая маска с логикой отключения уведомлений на определенное время
			"snooze_mask" => 0,
		],
		2 => [

			"snooze_mask" => 0, // побитовая маска с логикой отключения уведомлений на определенное время
			"is_snoozed"  => 0, // флаг 0/1 выключены ли уведомления в компании (0 - включены; 1 - выключены)
		],
	];

	/**
	 * Включает/отключает (с таймером) отправку уведомлений на определенное событие
	 */
	public static function snoozeForEvent(array $extra, int $event_mask, bool $is_snoozed):array {

		// формируем экстра данные
		return self::_prepareSnoozeMask($event_mask, $is_snoozed, $extra);
	}

	/**
	 * Включает/отключает (с таймером) отправку уведомлений на определенное событие
	 */
	public static function unsnoozeAllEvents(array $extra):array {

		// формируем экстра данные
		$extra = self::getExtra($extra);

		// обнуляем маску (все ивенты не замьючены)
		$extra["extra"]["snooze_mask"] = 0;

		return $extra;
	}

	/**
	 * устанавливаем флаг is_snoozed для уведомлений
	 */
	public static function setFlagSnoozed(array $extra, bool $is_snoozed):array {

		$extra = self::getExtra($extra);

		// устанавливаем флаг
		$extra["extra"]["is_snoozed"] = $is_snoozed === true ? 1 : 0;

		return $extra;
	}

	/**
	 * выключаем уведомления
	 */
	public static function setSnoozed(array $extra):array {

		return self::setFlagSnoozed($extra, self::USER_NOTIFICATION_IS_SNOOZED);
	}

	/**
	 * включаем уведомления
	 */
	public static function setNotSnoozed(array $extra):array {

		return self::setFlagSnoozed($extra, self::USER_NOTIFICATION_IS_NOT_SNOOZED);
	}

	/**
	 * получаем флаг is_snoozed для уведомлений
	 */
	public static function isFlagSnoozed(array $extra):bool {

		$extra = self::getExtra($extra);

		return $extra["extra"]["is_snoozed"] == 1;
	}

	/**
	 * Переключаем временный мут для определенных типов событий
	 */
	protected static function _prepareSnoozeMask(int $event_mask, int $is_snoozed, array $extra):array {

		// получаем актуальное extra
		$extra = self::getExtra($extra);

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
	 * Проверить отключены ли уведомления для ивента
	 */
	public static function isSnoozedForEvent(int $event_mask, array $extra):bool {

		// получаем актуальное extra
		$extra = self::getExtra($extra);

		return $extra["extra"]["snooze_mask"] & $event_mask;
	}

	/**
	 * Инициализировать extra
	 */
	public static function initExtra():array {

		return [
			"handler_version" => self::_EXTRA_VERSION,
			"extra"           => self::_EXTRA_SCHEMA[self::_EXTRA_VERSION],
		];
	}

	/**
	 * Актуализирует структуру extra
	 */
	public static function getExtra(array $extra):array {

		if (!isset($extra["handler_version"])) {
			return self::initExtra();
		}

		// сравниваем версию пришедшей extra с текущей
		if ($extra["handler_version"] != self::_EXTRA_VERSION) {

			// сливаем текущую версию extra и ту, что пришла
			$extra["extra"]           = array_merge(self::_EXTRA_SCHEMA[self::_EXTRA_VERSION], $extra["extra"]);
			$extra["handler_version"] = self::_EXTRA_VERSION;
		}
		return $extra;
	}
}