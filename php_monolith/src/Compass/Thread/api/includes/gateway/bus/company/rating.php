<?php

namespace Compass\Thread;

/**
 * класс для работы с rating
 */
class Gateway_Bus_Company_Rating extends  Gateway_Bus_Company_Main {

	public const CONVERSATION_MESSAGE = "conversation_message";
	public const THREAD_MESSAGE       = "thread_message";
	public const REACTION             = "reaction";
	public const FILE                 = "file";
	public const CALL                 = "call";
	public const VOICE                = "voice";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * инкремент статистики
	 *
	 * @param int    $value optional
	 *
	 */
	public static function inc(string $event, int $user_id, int $value = 1):void {

		// проверяем пришедшее значение value
		if ($value < 1) {
			return;
		}

		// формируем массив для запроса
		$ar_post = [
			"method"  => "rating.inc",
			"user_id" => $user_id,
			"event"   => $event,
			"inc"     => $value,
			"company_id" => COMPANY_ID,
		];

		// отправляем задачу в очередь
		Gateway_Bus_Rabbit::sendMessage(self::_QUEUE_NAME, $ar_post);
	}
}
