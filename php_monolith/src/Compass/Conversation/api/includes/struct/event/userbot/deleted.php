<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Структура события удаления бота
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Userbot_Deleted extends Struct_Default {

	/** @var string ид бота */
	public string $userbot_id;
	public int    $userbot_user_id;

	/** @var array ид пользователей */
	public array $user_id_list;

	public array $conversation_map_list;
	public int   $deleted_at;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(string $userbot_id, int $userbot_user_id, array $user_id_list, array $conversation_map_list, int $deleted_at):static {

		return new static([
			"userbot_id"            => $userbot_id,
			"userbot_user_id"       => $userbot_user_id,
			"user_id_list"          => $user_id_list,
			"conversation_map_list" => $conversation_map_list,
			"deleted_at"            => $deleted_at,
		]);
	}
}
