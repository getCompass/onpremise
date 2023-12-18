<?php

namespace Compass\Company;

/**
 * Класс для работы с аналитикой действий пространства
 */
class Type_Space_ActionAnalytics {

	protected const _EVENT_KEY = "space_action";

	public const CREATED                    = 1; // Пространство создано
	public const START_TRIAL                = 2; // В пространстве Запущен триал
	public const END_TRIAL                  = 3; // Триал окончен
	public const NEW_MEMBER                 = 4; // Новый участник пространства
	public const DISMISS_MEMBER             = 5; // Удален участник
	public const NEW_ADMINISTRATOR          = 6; // Назначен новый администратор пространства
	public const DISMISS_ADMINISTRATOR      = 7; // Администратор разжалован до пользователя
	public const SPACE_PAYED                = 8; // Пространство оплачено
	public const DEACTIVATION_TIMER_STARTED = 9; // Пространство не оплачено вовремя (пошел таймер деактивации)
	public const BLOCKED                    = 10; // Пространство заблокировано (время таймера истекло, пространство заблокировано)
	public const DELETED                    = 11; // Пространство удалено

	/**
	 * Пишем аналитику по действиям пользователя
	 */
	public static function send(int $space_id, int $user_id, int $action):void {

		if (isTestServer() && !isBackendTest() && !isLocalServer()) {
			return;
		}

		Gateway_Bus_CollectorAgent::init()->log(self::_EVENT_KEY, [
			"space_id"   => $space_id,
			"created_at" => time(),
			"action"     => $action,
			"user_id"    => $user_id,
		]);
	}
}