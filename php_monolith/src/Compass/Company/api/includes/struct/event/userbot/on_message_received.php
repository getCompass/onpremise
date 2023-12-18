<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Базовая структура события «пользовательский бот получил сообщение».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Userbot_OnMessageReceived extends Struct_Default {

	public array  $userbot_id_list;
	public int    $sender_id;
	public array  $message_text_list;
	public string $conversation_map;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(array $userbot_id_list, int $sender_id, array $message_text_list, string $conversation_map):static {

		return new static([
			"userbot_id_list"   => $userbot_id_list,
			"sender_id"         => $sender_id,
			"message_text_list" => $message_text_list,
			"conversation_map"  => $conversation_map,
		]);
	}
}
