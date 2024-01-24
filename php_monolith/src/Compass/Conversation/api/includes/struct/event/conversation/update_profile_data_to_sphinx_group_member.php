<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Структура события обновление conversation_name группового диалога
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Conversation_UpdateProfileDataToSphinxGroupMember extends Struct_Default {

	public int $user_id;

	public array $user_info;

	public string $conversation_map_list;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 * @ehrows \parseException
	 */
	public static function build(int $user_id, array $user_info, array $conversation_map_list):static {

		return new static([
			"user_id"               => $user_id,
			"user_info"             => $user_info,
			"conversation_map_list" => $conversation_map_list,
		]);
	}
}
