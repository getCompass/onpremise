<?php

namespace Compass\Jitsi;

/**
 * возможные статусы участников конференции
 */
enum Domain_Jitsi_Entity_ConferenceMember_Status: int {

	case JOINING = 1; // успешно сгенерировали токен для вступления в конференцию
	case SPEAKING = 2; // успешно вступил в конференцию (ориентируемся на события, отправляемые со стороны jitsi инстанса, который обслуживает конференцию)
	case DENIED_CAUSE_NO_SPACE_MEMBER = 8; // отказано в доступе; попытка вступить в закрытую конференцию будучи не являясь участником пространства(saas)/сервера(on-premise)
	case DENIED_CAUSE_IS_GUEST = 9; // отказано в доступе; попытка вступить в закрытую конференцию будучи гостем
	case LEFT = 10; // покинул конференцию
	case REJECTED = 11; // звонок отклонили
	case IGNORED = 12; // звонок проигнорировали
	case ACCEPTED = 13; // звонок принят

	public const ACCEPT_STATUS_DIALING  = "dialing";
	public const ACCEPT_STATUS_REJECTED = "rejected";
	public const ACCEPT_STATUS_IGNORED  = "ignored";
	public const ACCEPT_STATUS_ACCEPTED = "accepted";

	/**
	 * Получить информацию о том, принял ли участник звонок
	 * @return string
	 */
	public function getAcceptStatusOutput():string {

		return match ($this) {
			self::JOINING => self::ACCEPT_STATUS_DIALING,
			self::REJECTED => self::ACCEPT_STATUS_REJECTED,
			self::IGNORED => self::ACCEPT_STATUS_IGNORED,
			default => self::ACCEPT_STATUS_ACCEPTED,
		};
	}
}