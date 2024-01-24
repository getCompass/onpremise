<?php

declare(strict_types = 1);

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Базовая структура события «требуется парсинг ссылки».
 * Работает с Invite, но не Invitation.
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Conversation_LinkParseRequired extends Struct_Default {

	public string $message_map;
	public int    $user_id;
	public array  $link_list;
	public string $lang;
	public array  $user_list;
	public array  $entity_info;
	public bool   $need_full_preview;

	/**
	 * Статический конструктор.
	 *
	 * @param string $message_map
	 * @param int    $user_id
	 * @param array  $link_list
	 * @param string $lang
	 * @param array  $user_list
	 * @param bool   $need_full_preview
	 * @param array  $entity_info
	 *
	 * @return Struct_Event_Conversation_LinkParseRequired
	 * @throws ParseFatalException
	 */
	public static function build(string $message_map, int $user_id, array $link_list, string $lang, array $user_list, array $entity_info, bool $need_full_preview):static {

		return new static([
			"message_map"       => $message_map,
			"user_id"           => $user_id,
			"link_list"         => $link_list,
			"lang"              => $lang,
			"user_list"         => $user_list,
			"need_full_preview" => $need_full_preview,
			"entity_info"       => $entity_info,
		]);
	}
}
