<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Структура события обновление conversation_name группового диалога
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Conversation_UpdateAllowStatusAliasInLeftMenu extends Struct_Default {

	public int $allow_status;

	public array $extra;

	public string $conversation_map;

	public int $user_id;

	public int $opponent_user_id;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 * @ehrows \parseException
	 */
	public static function build(int $allow_status, array $extra, string $conversation_map, int $user_id, int $opponent_user_id):static {

		return new static([
			"allow_status"       => $allow_status,
			"extra"            => $extra,
			"conversation_map" => $conversation_map,
			"user_id"          => $user_id,
			"opponent_user_id" => $opponent_user_id,
		]);
	}
}
