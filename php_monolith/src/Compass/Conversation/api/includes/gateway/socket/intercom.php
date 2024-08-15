<?php

namespace Compass\Conversation;

use BaseFrame\Server\ServerProvider;

/**
 * Класс-интерфейс для работы с модулем php_intercom
 */
class Gateway_Socket_Intercom extends Gateway_Socket_Default {

	public const SYSTEM_EDIT_MESSAGE          = "SYSTEM: ОТРЕДАКТИРОВАЛ СООБЩЕНИЕ";
	public const SYSTEM_DELETE_MESSAGE        = "SYSTEM: УДАЛИЛ СООБЩЕНИЕ";
	public const SYSTEM_CREATE_REMIND_MESSAGE = "SYSTEM: УСТАНОВИЛ НАПОМИНАНИЕ";
	public const SYSTEM_SEND_REMIND_MESSAGE   = "SYSTEM: СРАБОТАЛО НАПОМИНАНИЕ";

	/**
	 * Добавляем сообщение в очередь
	 */
	public static function addMessageListToQueue(string $conversation_key, string $ip, string $user_agent, array $message_list):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$ar_post = [
			"conversation_key" => $conversation_key,
			"ip"               => $ip,
			"user_agent"       => $user_agent,
			"message_list"     => $message_list,
		];
		self::_doCallSocket("messages.addList", $ar_post);
	}

	/**
	 * Создаем диалог с пользователем
	 *
	 * @param int    $user_id
	 * @param int    $space_id
	 * @param string $conversation_key
	 * @param string $ip
	 * @param string $user_agent
	 *
	 * @return void
	 */
	public static function createConversation(int $user_id, int $space_id, string $conversation_key, string $ip, string $user_agent):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$ar_post = [
			"user_id"          => $user_id,
			"space_id"         => $space_id,
			"conversation_key" => $conversation_key,
			"ip"               => $ip,
			"user_agent"       => $user_agent,
		];
		self::_doCallSocket("user.createConversation", $ar_post);
	}

	/**
	 * Создаем контакт с пользователем
	 *
	 * @param int $user_id
	 *
	 * @return void
	 */
	public static function createContact(int $user_id):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$ar_post = [
			"user_id" => $user_id,
		];
		self::_doCallSocket("user.createContact", $ar_post);
	}

	// выполняем socket запрос
	protected static function _doCallSocket(string $method, array $params, int $user_id = 0):array {

		// получаем url и подпись
		$url = self::_getSocketIntercomUrl("intercom");
		return self::_doCall($url, $method, $params, $user_id);
	}
}
