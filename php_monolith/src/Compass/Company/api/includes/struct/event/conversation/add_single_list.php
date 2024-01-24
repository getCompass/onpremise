<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Структура события обновление conversation_name группового диалога
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Conversation_AddSingleList extends Struct_Default {

	public int   $user_id;
	public array $opponent_user_id_list;
	public int   $is_hidden_for_user;
	public int   $is_hidden_for_opponent;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 */
	public static function build(int $user_id, array $opponent_user_id_list, int $is_hidden_for_user, int $is_hidden_for_opponent,):static {

		return new static([
			"user_id"                => $user_id,
			"opponent_user_id_list"  => $opponent_user_id_list,
			"is_hidden_for_user"     => $is_hidden_for_user,
			"is_hidden_for_opponent" => $is_hidden_for_opponent,
		]);
	}
}
