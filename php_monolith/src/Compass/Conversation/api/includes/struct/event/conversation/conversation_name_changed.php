<?php

declare(strict_types = 1);

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Базовая структура события «приглашение отправлено».
 * Работает с Invite, но не Invitation.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Conversation_ConversationNameChanged extends Struct_Default {

	/** @var string conversation_map */
	public string $conversation_map;

	/** @var string conversation_map */
	public string $conversation_name;

	/**
	 * Статический конструктор.
	 *
	 * @param string $conversation_map
	 * @param string $conversation_name
	 *
	 * @return Struct_Event_Conversation_ConversationNameChanged
	 * @throws ParseFatalException
	 */
	public static function build(string $conversation_map, string $conversation_name):static {

		return new static([
			"conversation_name" => $conversation_name,
			"conversation_map"  => $conversation_map,
		]);
	}
}
