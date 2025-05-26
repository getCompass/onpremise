<?php declare(strict_types = 1);

namespace Compass\Thread;


/**
 * Структура события «необходимо удалить просроченные записи о прочитавших сообщения участниках».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Thread_ClearExpiredMessageReadParticipants extends Struct_Default {

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build():static {

		return new static([]);
	}
}
