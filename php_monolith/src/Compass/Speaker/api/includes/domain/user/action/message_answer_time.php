<?php

namespace Compass\Speaker;

use BaseFrame\Exception\Gateway\BusFatalException;

/**
 * Действие для работы с временем ответа на сообщения
 */
class Domain_User_Action_MessageAnswerTime {

	/**
	 * Закрываем микро-диалог
	 *
	 * @param string $conversation_key
	 * @param int    $user_id
	 * @param array  $receiver_user_id_list
	 *
	 * @return void
	 * @throws BusFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function closeMicroConversation(string $conversation_key, int $user_id, array $receiver_user_id_list):void {

		// закрываем микро-диалог
		Gateway_Bus_Rating_Main::closeMicroConversation($conversation_key, $user_id, $receiver_user_id_list);
	}
}