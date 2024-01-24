<?php

declare(strict_types = 1);

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Базовая структура события «пользователь вступил в диалог».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_UserConversation_UserJoinedConversation extends Struct_Default {

	/** @var int ид пользователя */
	public int $user_id;

	/** @var string ид диалога */
	public string $conversation_map;

	/** @var int роль в диалоге */
	public int $role;

	/** @var int дата вступления в диалог */
	public int $joined_at;

	/** @var bool является ли вступление тихим, без необходимсоти обновлять левое меню */
	public bool $is_silent_joining;

	/**
	 * Статический конструктор.
	 *
	 * @param int    $user_id
	 * @param string $conversation_map
	 * @param int    $role
	 * @param int    $joined_at
	 * @param bool   $is_silent_joining
	 *
	 * @return Struct_Event_UserConversation_UserJoinedConversation
	 * @throws ParseFatalException
	 */
	public static function build(int $user_id, string $conversation_map, int $role, int $joined_at, bool $is_silent_joining):static {

		return new static([
			"user_id"           => $user_id,
			"conversation_map"  => $conversation_map,
			"joined_at"         => $joined_at,
			"role"              => $role,
			"is_silent_joining" => $is_silent_joining,
		]);
	}
}
