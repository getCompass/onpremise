<?php

namespace Compass\Pivot;

/**
 * Класс для работы с аналитикой действий пространства
 */
class Type_Space_ActionAnalytics {

	/** @var int ID пользователя, по которому отправляем аналитику */
	protected int $_user_id;

	/** @var bool Флаг, нужно ли отправлять аналитику по этому пользователю */
	protected bool $_should_send_analytics;

	protected static ?self $_instance = null;

	protected function __construct(int $user_id) {

		// получаем информацию о пользователе
		$user_info = Gateway_Bus_PivotCache::getUserInfo($user_id);

		$this->_user_id               = $user_id;
		$this->_should_send_analytics = !Domain_User_Entity_User::isQATestUser($user_info);
	}

	/**
	 * @return static
	 */
	public static function init(int $user_id):self {

		if (is_null(self::$_instance)) {
			self::$_instance = new self($user_id);
		}

		return self::$_instance;
	}

	protected const _EVENT_KEY = "space_action";

	/** список всех логируемых действий */
	public const CREATED                    = 1; // Пространство создано
	public const START_TRIAL                = 2; // В пространстве Запущен триал
	public const END_TRIAL                  = 3; // Триал окончен
	public const NEW_MEMBER                 = 4; // Новый участник пространства
	public const DISMISS_MEMBER             = 5; // Удален участник
	public const NEW_ADMINISTRATOR          = 6; // Назначен новый администратор пространства
	public const DISMISS_ADMINISTRATOR      = 7; // Администратор разжалован до пользователя
	public const PAYED                      = 8; // Пространство оплачено
	public const DEACTIVATION_TIMER_STARTED = 9; // Пространство не оплачено вовремя (пошел таймер деактивации)
	public const BLOCKED                    = 10; // Пространство заблокировано (время таймера истекло, пространство заблокировано)
	public const DELETED                    = 11; // Пространство удалено

	/**
	 * Пишем аналитику по действиям пользователя
	 */
	public function send(int $space_id, int $action):void {

		if (isTestServer() && !isBackendTest() && !isLocalServer()) {
			return;
		}

		// если не нужно отправлять аналитику по этому пользователю
		if (!$this->_should_send_analytics) {
			return;
		}

		Gateway_Bus_CollectorAgent::init()->log(self::_EVENT_KEY, [
			"space_id"   => $space_id,
			"created_at" => time(),
			"action"     => $action,
			"user_id"    => $this->_user_id,
		]);
	}
}