<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Структура события редактирования бота
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Userbot_Edited extends Struct_Default {

	public array $userbot;
	public int   $userbot_user_id;

	/** @var array ид пользователей */
	public array $user_id_list;

	public array $conversation_map_list;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(array $userbot, int $userbot_user_id, array $user_id_list, array $conversation_map_list):static {

		return new static([
			"userbot"               => $userbot,
			"userbot_user_id"       => $userbot_user_id,
			"user_id_list"          => $user_id_list,
			"conversation_map_list" => $conversation_map_list,
		]);
	}
}
